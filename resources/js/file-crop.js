function createSimpleCrop(container, src, options = {}) {
    const viewportSize = options.viewportSize ?? 220;
    const containerSize = options.containerSize ?? 320;
    const shape = options.shape ?? 'circle';

    const root = document.createElement('div');
    root.dir = 'ltr';
    root.style.width = `${containerSize}px`;

    const stage = document.createElement('div');
    stage.style.cssText = `position:relative;width:${containerSize}px;height:${containerSize}px;overflow:hidden;background:#171717;border-radius:8px;touch-action:none;cursor:grab;`;

    const img = document.createElement('img');
    img.src = src;
    img.alt = 'crop';
    img.draggable = false;
    img.style.cssText = 'position:absolute;top:0;left:0;z-index:1;max-width:none;max-height:none;user-select:none;will-change:transform;';

    const mask = document.createElement('div');
    mask.style.cssText = [
        'position:absolute',
        `width:${viewportSize}px`,
        `height:${viewportSize}px`,
        `left:${(containerSize - viewportSize) / 2}px`,
        `top:${(containerSize - viewportSize) / 2}px`,
        'box-shadow:0 0 0 9999px rgba(0,0,0,.55)',
        'border:2px solid #fff',
        'pointer-events:none',
        'z-index:2',
        shape === 'circle' ? 'border-radius:50%' : '',
    ].join(';');

    const slider = document.createElement('input');
    slider.type = 'range';
    slider.min = '0';
    slider.max = '100';
    slider.value = '0';
    slider.setAttribute('aria-label', 'zoom');
    slider.style.cssText = 'width:100%;margin-top:12px;direction:ltr;';

    let scale = 1;
    let minScale = 1;
    let maxScale = 3;
    let x = 0;
    let y = 0;
    let dragging = false;
    let startX = 0;
    let startY = 0;
    let originX = 0;
    let originY = 0;

    const viewportLeft = () => (containerSize - viewportSize) / 2;
    const viewportTop = () => (containerSize - viewportSize) / 2;

    const applyTransform = () => {
        img.style.transform = `translate(${x}px, ${y}px) scale(${scale})`;
        img.style.transformOrigin = '0 0';
    };

    const clampPosition = () => {
        const width = img.naturalWidth * scale;
        const height = img.naturalHeight * scale;
        const left = viewportLeft();
        const top = viewportTop();
        const right = left + viewportSize;
        const bottom = top + viewportSize;

        x = Math.min(left, Math.max(right - width, x));
        y = Math.min(top, Math.max(bottom - height, y));
    };

    const syncSlider = () => {
        slider.value = String(((scale - minScale) / (maxScale - minScale)) * 100);
    };

    const fitImage = () => {
        if (! img.naturalWidth || ! img.naturalHeight) {
            return;
        }

        minScale = Math.max(viewportSize / img.naturalWidth, viewportSize / img.naturalHeight);
        maxScale = minScale * 3;
        scale = minScale;
        x = (containerSize - img.naturalWidth * scale) / 2;
        y = (containerSize - img.naturalHeight * scale) / 2;
        clampPosition();
        applyTransform();
        syncSlider();
    };

    img.addEventListener('load', fitImage);
    img.addEventListener('error', () => {
        container.innerHTML = '<p class="text-center text-sm text-red-500" dir="rtl">تعذّر عرض الصورة. جرّب JPG أو PNG.</p>';
    });

    stage.appendChild(img);
    stage.appendChild(mask);
    root.appendChild(stage);
    root.appendChild(slider);
    container.appendChild(root);

    if (img.complete && img.naturalWidth) {
        fitImage();
    }

    slider.addEventListener('input', () => {
        const t = Number(slider.value) / 100;
        const centerX = containerSize / 2;
        const centerY = containerSize / 2;
        const nextScale = minScale + (maxScale - minScale) * t;
        const ratio = nextScale / scale;

        x = centerX - (centerX - x) * ratio;
        y = centerY - (centerY - y) * ratio;
        scale = nextScale;
        clampPosition();
        applyTransform();
    });

    stage.addEventListener('pointerdown', (event) => {
        dragging = true;
        stage.setPointerCapture(event.pointerId);
        startX = event.clientX;
        startY = event.clientY;
        originX = x;
        originY = y;
        stage.style.cursor = 'grabbing';
    });

    stage.addEventListener('pointermove', (event) => {
        if (! dragging) {
            return;
        }

        x = originX + (event.clientX - startX);
        y = originY + (event.clientY - startY);
        clampPosition();
        applyTransform();
    });

    const endDrag = () => {
        dragging = false;
        stage.style.cursor = 'grab';
    };

    stage.addEventListener('pointerup', endDrag);
    stage.addEventListener('pointercancel', endDrag);

    stage.addEventListener('wheel', (event) => {
        event.preventDefault();
        const delta = event.deltaY > 0 ? -0.08 : 0.08;
        const centerX = containerSize / 2;
        const centerY = containerSize / 2;
        const nextScale = Math.min(maxScale, Math.max(minScale, scale * (1 + delta)));
        const ratio = nextScale / scale;

        x = centerX - (centerX - x) * ratio;
        y = centerY - (centerY - y) * ratio;
        scale = nextScale;
        clampPosition();
        applyTransform();
        syncSlider();
    }, { passive: false });

    const toBlob = (outputSize = 512, type = 'image/jpeg', quality = 0.92) => new Promise((resolve, reject) => {
        if (! img.complete || ! img.naturalWidth) {
            reject(new Error('Image not loaded'));

            return;
        }

        const sx = (viewportLeft() - x) / scale;
        const sy = (viewportTop() - y) / scale;
        const sWidth = viewportSize / scale;
        const sHeight = viewportSize / scale;

        const canvas = document.createElement('canvas');
        canvas.width = outputSize;
        canvas.height = outputSize;
        const context = canvas.getContext('2d');

        if (! context) {
            reject(new Error('Canvas unavailable'));

            return;
        }

        if (shape === 'circle') {
            context.beginPath();
            context.arc(outputSize / 2, outputSize / 2, outputSize / 2, 0, Math.PI * 2);
            context.closePath();
            context.clip();
        }

        context.drawImage(img, sx, sy, sWidth, sHeight, 0, 0, outputSize, outputSize);

        canvas.toBlob((blob) => {
            if (blob) {
                resolve(blob);
            } else {
                reject(new Error('Export failed'));
            }
        }, type, quality);
    });

    const destroy = () => {
        root.remove();
    };

    return { toBlob, destroy };
}

document.addEventListener('alpine:init', () => {
    Alpine.data('fileCrop', (config) => {
        let cropInstance = null;

        return {
            open: false,
            cropping: false,
            pendingSrc: null,
            previewUrl: config.previewUrl,
            wireName: config.wireName,
            outputSize: config.outputSize,
            shape: config.shape,
            cropHostId: `crop-host-${config.wireName}-${Math.random().toString(36).slice(2, 9)}`,

            pickFile() {
                window.dispatchEvent(new CustomEvent('file-crop-opened'));

                const input = this.$refs.fileInput;

                const onWindowFocus = () => {
                    window.removeEventListener('focus', onWindowFocus);

                    window.setTimeout(() => {
                        if (! this.open && ! input.files?.length) {
                            window.dispatchEvent(new CustomEvent('file-crop-closed'));
                        }
                    }, 400);
                };

                window.addEventListener('focus', onWindowFocus);
                input.click();
            },

            cropHost() {
                return document.getElementById(this.cropHostId);
            },

            setPreviewFromBlob(blob) {
                if (this.previewUrl?.startsWith('blob:')) {
                    URL.revokeObjectURL(this.previewUrl);
                }

                this.previewUrl = URL.createObjectURL(blob);
            },

            openCropper(event) {
                const file = event.target.files?.[0];
                event.target.value = '';

                if (! file) {
                    window.dispatchEvent(new CustomEvent('file-crop-closed'));

                    return;
                }

                const reader = new FileReader();

                reader.onload = (loadEvent) => {
                    this.pendingSrc = loadEvent.target?.result;

                    if (! this.pendingSrc) {
                        return;
                    }

                    this.open = true;

                    this.$nextTick(() => {
                        requestAnimationFrame(() => this.mountCropper());
                    });
                };

                reader.onerror = () => {
                    this.pendingSrc = null;
                };

                reader.readAsDataURL(file);
            },

            mountCropper() {
                const host = this.cropHost();

                if (! this.open || ! this.pendingSrc || ! host) {
                    return;
                }

                this.teardownCropper();

                cropInstance = createSimpleCrop(host, this.pendingSrc, {
                    viewportSize: 220,
                    containerSize: 320,
                    shape: this.shape,
                });
            },

            teardownCropper() {
                cropInstance?.destroy();
                cropInstance = null;

                const host = this.cropHost();

                if (host) {
                    host.innerHTML = '';
                }
            },

            closeCropper() {
                this.open = false;
                this.pendingSrc = null;
                this.teardownCropper();
                window.dispatchEvent(new CustomEvent('file-crop-closed'));
            },

            confirmCrop() {
                if (! cropInstance || this.cropping) {
                    return;
                }

                this.cropping = true;

                const activeCrop = cropInstance;

                activeCrop.toBlob(this.outputSize, 'image/jpeg', 0.92)
                    .then((blob) => {
                        this.setPreviewFromBlob(blob);

                        const file = new File([blob], 'logo.jpg', { type: 'image/jpeg' });

                        return new Promise((resolve, reject) => {
                            this.$wire.$upload(
                                this.wireName,
                                file,
                                () => resolve(),
                                (error) => reject(error),
                                () => {},
                            );
                        });
                    })
                    .then(() => this.closeCropper())
                    .finally(() => {
                        this.cropping = false;
                    });
            },
        };
    });
});
