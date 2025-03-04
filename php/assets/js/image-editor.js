class ImageEditor {
    constructor(options) {
        this.previewContainer = options.previewContainer;
        this.fileInput = options.fileInput;
        this.cropButton = options.cropButton;
        this.rotateLeftButton = options.rotateLeftButton;
        this.rotateRightButton = options.rotateRightButton;
        this.flipHorizontalButton = options.flipHorizontalButton;
        this.flipVerticalButton = options.flipVerticalButton;
        this.resetButton = options.resetButton;
        this.saveButton = options.saveButton;
        this.onSave = options.onSave;

        this.cropper = null;
        this.originalImage = null;

        this.init();
    }

    init() {
        this.fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
        this.cropButton.addEventListener('click', () => this.crop());
        this.rotateLeftButton.addEventListener('click', () => this.rotate(-90));
        this.rotateRightButton.addEventListener('click', () => this.rotate(90));
        this.flipHorizontalButton.addEventListener('click', () => this.flip('horizontal'));
        this.flipVerticalButton.addEventListener('click', () => this.flip('vertical'));
        this.resetButton.addEventListener('click', () => this.reset());
        this.saveButton.addEventListener('click', () => this.save());
    }

    handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.originalImage = e.target.result;
                this.previewContainer.src = this.originalImage;
                this.initCropper();
            };
            reader.readAsDataURL(file);
        }
    }

    initCropper() {
        if (this.cropper) {
            this.cropper.destroy();
        }

        this.cropper = new Cropper(this.previewContainer, {
            aspectRatio: 1,
            viewMode: 1,
            autoCropArea: 1,
            responsive: true,
            background: false,
            zoomable: true,
            scalable: true
        });
    }

    crop() {
        if (!this.cropper) return;
        const canvas = this.cropper.getCroppedCanvas();
        this.previewContainer.src = canvas.toDataURL();
        this.initCropper();
    }

    rotate(degree) {
        if (!this.cropper) return;
        this.cropper.rotate(degree);
    }

    flip(direction) {
        if (!this.cropper) return;
        if (direction === 'horizontal') {
            this.cropper.scaleX(this.cropper.getData().scaleX * -1);
        } else {
            this.cropper.scaleY(this.cropper.getData().scaleY * -1);
        }
    }

    reset() {
        if (!this.cropper) return;
        this.cropper.reset();
    }

    save() {
        if (!this.cropper) return;
        const canvas = this.cropper.getCroppedCanvas();
        const base64 = canvas.toDataURL();
        if (this.onSave) {
            this.onSave(base64);
        }
    }

    destroy() {
        if (this.cropper) {
            this.cropper.destroy();
            this.cropper = null;
        }
    }
}