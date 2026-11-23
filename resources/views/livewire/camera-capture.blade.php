<div x-data="{
        stream: null,
        captured: false,
        cameraError: '',
        rawError: '',
        showErrorDetails: false,
        preview: '',
        uploadPreview: '',
        uploadFile: null,
        cameraFileError: '',
        captureWarning: '',

        compressImage(source, callback) {
            const image = new Image();

            image.onload = () => {
                const maxDimension = 1600;
                const scale = Math.min(1, maxDimension / Math.max(image.naturalWidth, image.naturalHeight));
                const canvas = document.createElement('canvas');

                canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
                canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));

                const context = canvas.getContext('2d');
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                context.drawImage(image, 0, 0, canvas.width, canvas.height);

                callback(canvas.toDataURL('image/jpeg', 0.72));
            };

            image.onerror = () => {
                this.cameraFileError = 'تعذّر ضغط الصورة. يرجى اختيار صورة أخرى.';
            };

            image.src = source;
        },

        onFileSelected(event) {
            this.uploadPreview = '';
            this.uploadFile = null;

            const file = event.target.files && event.target.files[0];

            if (!file) {
                return;
            }

            if (!['image/jpeg', 'image/png'].includes(file.type)) {
                this.cameraFileError = 'نوع الملف غير مدعوم. يُقبل JPG أو PNG فقط.';
                event.target.value = '';

                return;
            }

            this.cameraFileError = '';
            this.uploadFile = file;

            const reader = new FileReader();
            reader.onload = () => {
                this.compressImage(reader.result, (compressed) => {
                    this.uploadPreview = compressed;
                    this.uploadFile = compressed;
                });
            };
            reader.readAsDataURL(file);
        },

        uploadConfirm() {
            if (!this.uploadPreview) {
                return;
            }
            this.$wire.saveImage(this.uploadPreview);
        },

        uploadCancel() {
            this.uploadPreview = '';
            this.uploadFile = null;
            this.$refs.fileInput.value = '';
        },

        startCamera() {
            this.cameraError = '';
            this.rawError = '';
            this.showErrorDetails = false;
            this.captureWarning = '';

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.cameraError = 'المتصفح لا يدعم الوصول إلى الكاميرا في هذا السياق (يتطلب اتصالاً آمناً).';
                this.rawError = 'NotSupportedError: navigator.mediaDevices.getUserMedia is not available';
                return;
            }

            const getUserMedia = navigator.mediaDevices.getUserMedia.bind(navigator.mediaDevices);

            getUserMedia({ video: { facingMode: 'environment' }, audio: false })
                .catch(() => getUserMedia({ video: true, audio: false }))
                .then((s) => {
                    this.stream = s;
                    this.$refs.video.srcObject = s;
                })
                .catch((err) => {
                    this.stream = null;
                    const name = (err && err.name) || 'Error';
                    const message = (err && err.message) || 'unknown error';
                    this.rawError = name + ': ' + message;
                    if (name === 'NotAllowedError' || name === 'PermissionDeniedError') {
                        this.cameraError = 'تعذّر الوصول إلى الكاميرا. يرجى منح إذن استخدام الكاميرا ثم أعد المحاولة.';
                    } else {
                        this.cameraError = this.rawError;
                    }
                });
        },

        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach((t) => t.stop());
                this.stream = null;
            }
        },

        capturePhoto() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;

            if (!video || video.readyState < 2 || !video.videoWidth) {
                this.captureWarning = 'لم تكن الكاميرا جاهزة بعد. يرجى الانتظار حتى تظهر الصورة المباشرة ثم أعد المحاولة.';
                return;
            }

            this.captureWarning = '';
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);

            const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
            let allBlack = true;
            for (let i = 0; i < data.length; i += 400) {
                if (data[i] > 10 || data[i + 1] > 10 || data[i + 2] > 10) {
                    allBlack = false;
                    break;
                }
            }

            if (allBlack) {
                this.captureWarning = 'تم التقاط صورة سوداء بالكامل. يرجى التأكد من الإضاءة وإطار الصورة ثم إعادة الالتقاط.';
                return;
            }

            this.compressImage(canvas.toDataURL('image/png'), (compressed) => {
                this.preview = compressed;
                this.captured = true;
                this.stopCamera();
            });
        },

        retake() {
            this.captured = false;
            this.preview = '';
            this.startCamera();
        },

        confirmCapture() {
            if (!this.preview) {
                return;
            }
            this.$wire.saveImage(this.preview);
        },

        beforeDestroy() {
            this.stopCamera();
        },
    }"
    x-init="startCamera()"
    class="dga-card">
    <div class="dga-card-body space-y-4" dir="rtl">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-ink">{{ $label }}</h3>

            @if ($existing)
                <span class="inline-flex items-center rounded-md bg-success/10 px-3 py-1 text-sm font-medium text-success">
                    تم الالتقاط ✓
                </span>
            @endif
        </div>

        <template x-if="!captured && !cameraError">
            <video x-ref="video" autoplay playsinline muted
                   class="mx-auto h-52 w-full max-w-md rounded-lg bg-black object-cover"></video>
        </template>

        <div x-show="captured" x-cloak>
            <img :src="preview" alt="معاينة الصورة"
                 class="mx-auto h-52 w-full max-w-md rounded-lg object-contain bg-surface" />
        </div>

        <div x-show="cameraError" x-cloak class="rounded-md bg-danger/10 border border-danger/25 p-3 text-danger text-sm space-y-2">
            <span x-text="cameraError"></span>
            <button type="button" x-show="rawError" x-on:click="showErrorDetails = !showErrorDetails"
                    class="text-xs underline text-danger" x-cloak>
                <span x-text="showErrorDetails ? 'إخفاء تفاصيل الخطأ' : 'تفاصيل الخطأ'"></span>
            </button>
            <div x-show="showErrorDetails && rawError" x-cloak class="text-xs font-mono break-all text-danger" dir="ltr">
                <span x-text="rawError"></span>
            </div>
        </div>

        <div x-show="!captured && !uploadPreview" x-cloak class="space-y-2">
            <button type="button" x-on:click="$refs.fileInput.click()"
                    x-class="{ 'ring-2 ring-danger ring-offset-1': cameraError }"
                    class="inline-flex items-center px-4 py-2 bg-white hover:bg-surface text-ink text-sm font-semibold rounded-md border border-line">
                رفع من الجهاز
            </button>
            <p x-show="cameraFileError" x-cloak x-text="cameraFileError"
               class="mt-2 text-sm text-danger"></p>
        </div>

        <input type="file" x-ref="fileInput" accept="image/jpeg,image/png" class="hidden"
               x-on:change="onFileSelected($event)" />

        <div x-show="uploadPreview" x-cloak class="space-y-3">
            <img :src="uploadPreview" alt="معاينة الصورة المرفوعة"
                 class="mx-auto h-52 w-full max-w-md rounded-lg object-contain bg-surface" />
            <div class="flex items-center gap-3">
                <button type="button" x-on:click="uploadCancel()"
                        class="inline-flex items-center px-4 py-2 bg-secondary hover:bg-secondary-dark text-white text-sm font-semibold rounded-md">
                    إلغاء
                </button>
                <button type="button" x-on:click="uploadConfirm()"
                        wire:target="saveImage" wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-md disabled:opacity-50">
                    <span wire:loading.remove>اعتماد</span>
                    <span wire:loading wire:target="saveImage" x-cloak>جارٍ الحفظ...</span>
                </button>
            </div>
        </div>

        <template x-if="!captured && !uploadPreview">
            <button type="button" x-on:click="capturePhoto()" :disabled="!stream"
                    class="inline-flex items-center px-4 py-2 bg-secondary hover:bg-secondary-dark text-white text-sm font-semibold rounded-md disabled:opacity-50">
                التقاط الصورة
            </button>
            <p x-show="captureWarning" x-cloak x-text="captureWarning"
               class="mt-2 text-sm text-navy"></p>
        </template>

        <template x-if="captured">
            <div class="flex items-center gap-3" x-cloak>
                <button type="button" x-on:click="retake()"
                        class="inline-flex items-center px-4 py-2 bg-secondary hover:bg-secondary-dark text-white text-sm font-semibold rounded-md">
                    إعادة الالتقاط
                </button>
                <button type="button" x-on:click="confirmCapture()"
                        wire:target="saveImage" wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-md disabled:opacity-50">
                    <span wire:loading.remove>تأكيد وحفظ</span>
                    <span wire:loading wire:target="saveImage" x-cloak>جارٍ الحفظ...</span>
                </button>
            </div>
        </template>

        <x-input-error class="mt-2" :messages="$errors->get('imageData')" />
    </div>
    <canvas x-ref="canvas" class="hidden"></canvas>
</div>
