import { createSimpleCrop, createFreeCrop } from './lib/image-crop-engine.js';

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
            defaultShape: config.shape,
            allowShapeSwitch: config.allowShapeSwitch ?? false,
            cropHostId: `crop-host-${config.wireName}-${Math.random().toString(36).slice(2, 9)}`,

            setCropShape(nextShape) {
                if (this.shape === nextShape) {
                    return;
                }

                this.shape = nextShape;
                this.mountCropper();
            },

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

                    if (this.allowShapeSwitch) {
                        this.shape = this.defaultShape;
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

                if (this.shape === 'free') {
                    cropInstance = createFreeCrop(host, this.pendingSrc, {
                        containerWidth: 360,
                        containerHeight: 400,
                        maxOutputSize: this.outputSize,
                    });
                } else {
                    cropInstance = createSimpleCrop(host, this.pendingSrc, {
                        viewportSize: 220,
                        containerSize: 320,
                        shape: this.shape,
                    });
                }
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

                const exportPromise = this.shape === 'free'
                    ? activeCrop.toBlob('image/jpeg', 0.92)
                    : activeCrop.toBlob(this.outputSize, 'image/jpeg', 0.92);

                exportPromise
                    .then((blob) => {
                        this.setPreviewFromBlob(blob);

                        const file = new File([blob], 'cropped.jpg', { type: 'image/jpeg' });

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
