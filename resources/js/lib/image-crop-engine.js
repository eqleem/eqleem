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

function createFreeCrop(container, src, options = {}) {
    const containerWidth = options.containerWidth ?? 360;
    const containerHeight = options.containerHeight ?? 400;
    const minCropSize = options.minCropSize ?? 48;
    const maxOutputSize = options.maxOutputSize ?? 1920;

    const root = document.createElement('div');
    root.dir = 'ltr';
    root.style.width = `${containerWidth}px`;

    const stage = document.createElement('div');
    stage.style.cssText = `position:relative;width:${containerWidth}px;height:${containerHeight}px;overflow:hidden;background:#171717;border-radius:8px;touch-action:none;`;

    const img = document.createElement('img');
    img.src = src;
    img.alt = 'crop';
    img.draggable = false;
    img.style.cssText = 'position:absolute;top:0;left:0;z-index:1;max-width:none;max-height:none;user-select:none;will-change:transform;pointer-events:none;';

    const cropBox = document.createElement('div');
    cropBox.style.cssText = 'position:absolute;z-index:3;border:2px solid #fff;box-shadow:0 0 0 9999px rgba(0,0,0,.55);cursor:move;touch-action:none;';

    const handles = ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w'].map((position) => {
        const handle = document.createElement('div');
        handle.dataset.handle = position;
        handle.style.cssText = [
            'position:absolute',
            'width:12px',
            'height:12px',
            'background:#fff',
            'border:1px solid #d1d5db',
            'border-radius:2px',
            'z-index:4',
            'touch-action:none',
        ].join(';');

        const cursorMap = {
            nw: 'nwse-resize',
            n: 'ns-resize',
            ne: 'nesw-resize',
            e: 'ew-resize',
            se: 'nwse-resize',
            s: 'ns-resize',
            sw: 'nesw-resize',
            w: 'ew-resize',
        };

        handle.style.cursor = cursorMap[position];
        cropBox.appendChild(handle);

        return handle;
    });

    const slider = document.createElement('input');
    slider.type = 'range';
    slider.min = '0';
    slider.max = '100';
    slider.value = '0';
    slider.setAttribute('aria-label', 'zoom');
    slider.style.cssText = 'width:100%;margin-top:12px;direction:ltr;';

    let imageScale = 1;
    let minImageScale = 1;
    let maxImageScale = 3;
    let imageX = 0;
    let imageY = 0;
    let cropX = 0;
    let cropY = 0;
    let cropW = 0;
    let cropH = 0;
    let dragging = false;
    let resizing = null;
    let startX = 0;
    let startY = 0;
    let originCropX = 0;
    let originCropY = 0;
    let originCropW = 0;
    let originCropH = 0;

    const imageBounds = () => ({
        left: imageX,
        top: imageY,
        right: imageX + img.naturalWidth * imageScale,
        bottom: imageY + img.naturalHeight * imageScale,
    });

    const clampCrop = () => {
        const bounds = imageBounds();

        cropW = Math.max(minCropSize, Math.min(cropW, bounds.right - bounds.left));
        cropH = Math.max(minCropSize, Math.min(cropH, bounds.bottom - bounds.top));
        cropX = Math.min(Math.max(cropX, bounds.left), bounds.right - cropW);
        cropY = Math.min(Math.max(cropY, bounds.top), bounds.bottom - cropH);
    };

    const positionHandles = () => {
        handles.forEach((handle) => {
            const position = handle.dataset.handle;
            const half = 6;

            const positions = {
                nw: { left: -half, top: -half },
                n: { left: cropW / 2 - half, top: -half },
                ne: { left: cropW - half, top: -half },
                e: { left: cropW - half, top: cropH / 2 - half },
                se: { left: cropW - half, top: cropH - half },
                s: { left: cropW / 2 - half, top: cropH - half },
                sw: { left: -half, top: cropH - half },
                w: { left: -half, top: cropH / 2 - half },
            };

            const coords = positions[position];
            handle.style.left = `${coords.left}px`;
            handle.style.top = `${coords.top}px`;
        });
    };

    const renderCropBox = () => {
        cropBox.style.left = `${cropX}px`;
        cropBox.style.top = `${cropY}px`;
        cropBox.style.width = `${cropW}px`;
        cropBox.style.height = `${cropH}px`;
        positionHandles();
    };

    const applyImageTransform = () => {
        img.style.transform = `translate(${imageX}px, ${imageY}px) scale(${imageScale})`;
        img.style.transformOrigin = '0 0';
    };

    const syncSlider = () => {
        slider.value = String(((imageScale - minImageScale) / (maxImageScale - minImageScale)) * 100);
    };

    const fitImage = () => {
        if (! img.naturalWidth || ! img.naturalHeight) {
            return;
        }

        const scaleX = containerWidth / img.naturalWidth;
        const scaleY = containerHeight / img.naturalHeight;
        minImageScale = Math.min(scaleX, scaleY);
        maxImageScale = minImageScale * 3;
        imageScale = minImageScale;
        imageX = (containerWidth - img.naturalWidth * imageScale) / 2;
        imageY = (containerHeight - img.naturalHeight * imageScale) / 2;

        const displayW = img.naturalWidth * imageScale;
        const displayH = img.naturalHeight * imageScale;
        cropW = displayW * 0.8;
        cropH = displayH * 0.6;
        cropX = imageX + (displayW - cropW) / 2;
        cropY = imageY + (displayH - cropH) / 2;

        clampCrop();
        applyImageTransform();
        renderCropBox();
        syncSlider();
    };

    img.addEventListener('load', fitImage);
    img.addEventListener('error', () => {
        container.innerHTML = '<p class="text-center text-sm text-red-500" dir="rtl">تعذّر عرض الصورة. جرّب JPG أو PNG.</p>';
    });

    stage.appendChild(img);
    stage.appendChild(cropBox);
    root.appendChild(stage);
    root.appendChild(slider);
    container.appendChild(root);

    if (img.complete && img.naturalWidth) {
        fitImage();
    }

    slider.addEventListener('input', () => {
        const t = Number(slider.value) / 100;
        const centerX = cropX + cropW / 2;
        const centerY = cropY + cropH / 2;
        const nextScale = minImageScale + (maxImageScale - minImageScale) * t;
        const ratio = nextScale / imageScale;

        imageX = centerX - (centerX - imageX) * ratio;
        imageY = centerY - (centerY - imageY) * ratio;
        imageScale = nextScale;
        clampCrop();
        applyImageTransform();
        renderCropBox();
    });

    const onPointerMove = (event) => {
        const deltaX = event.clientX - startX;
        const deltaY = event.clientY - startY;
        const bounds = imageBounds();

        if (dragging) {
            cropX = originCropX + deltaX;
            cropY = originCropY + deltaY;
            clampCrop();
            renderCropBox();

            return;
        }

        if (! resizing) {
            return;
        }

        let nextX = originCropX;
        let nextY = originCropY;
        let nextW = originCropW;
        let nextH = originCropH;

        if (resizing.includes('e')) {
            nextW = originCropW + deltaX;
        }

        if (resizing.includes('s')) {
            nextH = originCropH + deltaY;
        }

        if (resizing.includes('w')) {
            nextW = originCropW - deltaX;
            nextX = originCropX + deltaX;
        }

        if (resizing.includes('n')) {
            nextH = originCropH - deltaY;
            nextY = originCropY + deltaY;
        }

        if (nextW < minCropSize) {
            if (resizing.includes('w')) {
                nextX -= minCropSize - nextW;
            }

            nextW = minCropSize;
        }

        if (nextH < minCropSize) {
            if (resizing.includes('n')) {
                nextY -= minCropSize - nextH;
            }

            nextH = minCropSize;
        }

        if (nextX < bounds.left) {
            nextW -= bounds.left - nextX;
            nextX = bounds.left;
        }

        if (nextY < bounds.top) {
            nextH -= bounds.top - nextY;
            nextY = bounds.top;
        }

        if (nextX + nextW > bounds.right) {
            nextW = bounds.right - nextX;
        }

        if (nextY + nextH > bounds.bottom) {
            nextH = bounds.bottom - nextY;
        }

        cropX = nextX;
        cropY = nextY;
        cropW = nextW;
        cropH = nextH;
        clampCrop();
        renderCropBox();
    };

    const endInteraction = () => {
        dragging = false;
        resizing = null;
        window.removeEventListener('pointermove', onPointerMove);
        window.removeEventListener('pointerup', endInteraction);
        window.removeEventListener('pointercancel', endInteraction);
    };

    cropBox.addEventListener('pointerdown', (event) => {
        if (event.target !== cropBox) {
            return;
        }

        event.preventDefault();
        dragging = true;
        startX = event.clientX;
        startY = event.clientY;
        originCropX = cropX;
        originCropY = cropY;
        cropBox.setPointerCapture(event.pointerId);
        window.addEventListener('pointermove', onPointerMove);
        window.addEventListener('pointerup', endInteraction);
        window.addEventListener('pointercancel', endInteraction);
    });

    handles.forEach((handle) => {
        handle.addEventListener('pointerdown', (event) => {
            event.preventDefault();
            event.stopPropagation();
            resizing = handle.dataset.handle;
            startX = event.clientX;
            startY = event.clientY;
            originCropX = cropX;
            originCropY = cropY;
            originCropW = cropW;
            originCropH = cropH;
            handle.setPointerCapture(event.pointerId);
            window.addEventListener('pointermove', onPointerMove);
            window.addEventListener('pointerup', endInteraction);
            window.addEventListener('pointercancel', endInteraction);
        });
    });

    const toBlob = (type = 'image/jpeg', quality = 0.92) => new Promise((resolve, reject) => {
        if (! img.complete || ! img.naturalWidth) {
            reject(new Error('Image not loaded'));

            return;
        }

        const sx = (cropX - imageX) / imageScale;
        const sy = (cropY - imageY) / imageScale;
        const sWidth = cropW / imageScale;
        const sHeight = cropH / imageScale;

        let outW = Math.max(1, Math.round(sWidth));
        let outH = Math.max(1, Math.round(sHeight));
        const maxDim = Math.max(outW, outH);

        if (maxDim > maxOutputSize) {
            const ratio = maxOutputSize / maxDim;
            outW = Math.round(outW * ratio);
            outH = Math.round(outH * ratio);
        }

        const canvas = document.createElement('canvas');
        canvas.width = outW;
        canvas.height = outH;
        const context = canvas.getContext('2d');

        if (! context) {
            reject(new Error('Canvas unavailable'));

            return;
        }

        context.drawImage(img, sx, sy, sWidth, sHeight, 0, 0, outW, outH);

        canvas.toBlob((blob) => {
            if (blob) {
                resolve(blob);
            } else {
                reject(new Error('Export failed'));
            }
        }, type, quality);
    });

    const destroy = () => {
        endInteraction();
        root.remove();
    };

    return { toBlob, destroy };
}

export { createSimpleCrop, createFreeCrop };
