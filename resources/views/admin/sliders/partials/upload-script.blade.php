{{--
    Alpine component for the slider form.

    Posts the form over XHR instead of letting the browser do a normal submit,
    because only XHR reports upload progress. The server still answers with a
    normal redirect, so validation errors and the success toast behave exactly
    as they do without JavaScript: the final response URL is followed.
--}}
<script>
    function sliderUpload(limits) {
        return {
            limits: limits || {},
            xhr: null,
            uploading: false,
            phase: 'idle', // idle | uploading | processing
            progress: 0,
            progressVisible: false,
            sent: 0,
            total: 0,
            uploadError: '',
            selected: {},

            get statusText() {
                if (this.phase === 'processing') return 'Processing video…';
                return this.uploadName ? 'Uploading ' + this.uploadName : 'Uploading…';
            },

            get detailText() {
                if (this.phase === 'processing') {
                    return 'Upload finished. The server is storing the file now.';
                }
                if (!this.progressVisible || !this.total) return 'Starting upload…';
                return this.formatSize(this.sent) + ' of ' + this.formatSize(this.total);
            },

            /** The file currently chosen for one input, used in the status line. */
            get uploadName() {
                const picked = this.selected.video || this.selected.audio || this.selected.image;
                return picked ? picked.name : '';
            },

            /** What to print under one file input. */
            pickedFor(name) {
                const file = this.selected[name];
                return file ? file.name + ' — ' + this.formatSize(file.size) : '';
            },

            /** Each file input validates against its own limit before submitting. */
            limitFor(name) {
                return this.limits[name] || null;
            },

            checkFile(input) {
                const file = input.files && input.files[0];
                if (!file) return true;

                const limit = this.limitFor(input.name);
                if (!limit) return true;

                if (limit.accept && file.type && !this.matchesAccept(file.type, limit.accept)) {
                    this.uploadError = (limit.label || 'This file') + ' is not an accepted format.';
                    return false;
                }

                if (limit.maxBytes && file.size > limit.maxBytes) {
                    this.uploadError = (limit.label || 'This file') + ' is ' + this.formatSize(file.size)
                        + '. The limit is ' + this.formatSize(limit.maxBytes) + '.';
                    return false;
                }

                this.uploadError = '';
                return true;
            },

            matchesAccept(type, accept) {
                return accept
                    .split(',')
                    .map((entry) => entry.trim().toLowerCase())
                    .some((entry) => entry === type || entry === '*/*' || type.split('/')[0] + '/*' === entry);
            },

            trackFile(input) {
                const file = input.files && input.files[0];

                if (!file) {
                    this.selected = Object.assign({}, this.selected);
                    delete this.selected[input.name];
                    return;
                }

                this.selected = Object.assign({}, this.selected, {
                    [input.name]: { name: file.name, size: file.size },
                });

                this.checkFile(input);
            },

            submitForm(event) {
                const form = event.target;

                // A file left in a field that is now hidden (the admin picked a
                // video, then switched back to Image) is ignored by the server
                // too, so it must not fail the upload here.
                for (const input of form.querySelectorAll('input[type="file"]')) {
                    const field = input.closest('[id$="_field"]');
                    if (field && field.classList.contains('hidden')) continue;
                    if (!this.checkFile(input)) {
                        event.preventDefault();
                        return;
                    }
                }

                this.uploading = true;
                this.phase = 'uploading';
                this.progress = 0;
                this.progressVisible = false;
                this.sent = 0;
                this.total = 0;

                const xhr = new XMLHttpRequest();
                this.xhr = xhr;
                xhr.open('POST', form.action, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('X-CSRF-TOKEN', form.querySelector('input[name="_token"]').value);

                // Only the bytes leaving the browser are reported here. The
                // server still has to store the file afterwards, so 100% must
                // not be treated as "finished" on its own.
                xhr.upload.addEventListener('progress', (progressEvent) => {
                    if (!progressEvent.lengthComputable) return;

                    this.progressVisible = true;
                    this.sent = progressEvent.loaded;
                    this.total = progressEvent.total;
                    this.progress = Math.min(99, Math.round((progressEvent.loaded / progressEvent.total) * 100));

                    if (progressEvent.loaded >= progressEvent.total) {
                        this.phase = 'processing';
                        this.progress = 99;
                    }
                });

                xhr.addEventListener('load', () => {
                    this.phase = 'processing';

                    let data = null;
                    try {
                        data = JSON.parse(xhr.responseText);
                    } catch (error) {
                        data = null;
                    }

                    // The controller answers XHR with JSON saying exactly what
                    // happened. Anything else means the request never reached it
                    // (an expired session redirects to the login page), so the
                    // old "follow the redirect and hope" behaviour would hide
                    // the real problem.
                    if (!data) {
                        this.fail('The save did not complete. You may have been logged out — sign in again and retry.');
                        return;
                    }

                    if (xhr.status >= 200 && xhr.status < 300 && data.ok) {
                        this.progress = 100;
                        window.location.assign(data.redirect);
                        return;
                    }

                    this.fail(this.firstError(data));
                });

                xhr.addEventListener('error', () => this.fail('The upload was interrupted. Check your connection and try again.'));
                xhr.addEventListener('abort', () => this.fail('The upload was cancelled.'));

                xhr.send(new FormData(form));
            },

            firstError(data) {
                if (data && data.message) return data.message;

                if (data && data.errors) {
                    const first = Object.values(data.errors)[0];
                    if (Array.isArray(first) && first.length) return first[0];
                }

                return 'The slider could not be saved.';
            },

            fail(message) {
                this.uploading = false;
                this.phase = 'idle';
                this.progress = 0;
                this.progressVisible = false;
                this.uploadError = message;
            },

            formatSize(bytes) {
                if (!bytes) return '0 B';
                if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
                return Math.max(1, Math.round(bytes / 1024)) + ' KB';
            },

            dismiss() {
                this.uploading = false;
                this.phase = 'idle';
                this.progress = 0;
                this.progressVisible = false;
                this.uploadError = '';
                this.selected = {};
                this.xhr = null;
            },

            cancel() {
                if (this.xhr) this.xhr.abort();
                this.dismiss();
            },
        };
    }
</script>