<script>
    /**
     * Media picker, shared by every admin form that accepts a file.
     *
     * Two ways to fill a field: pick something already uploaded, or upload from
     * the PC. Both end up as a "<field>_media_id" value which MediaService
     * resolves next to the ordinary file input, so no controller has to change.
     */
    function mediaPicker(config) {
        return {
            field: config.field,
            // Set when the picker sits inside an Alpine loop, so the field name
            // has to follow the row, e.g. variations[3][image].
            fieldExpr: config.fieldExpr || null,
            // Where the picked media id should be written. Defaults to the
            // field name with "_media_id" inserted before the last "]", e.g.
            // variations[3][image] -> variations[3][image_media_id]. Override
            // when the id lives on its own key, e.g. images[0][media_id].
            mediaIdFieldExpr: config.mediaIdFieldExpr || null,
            kind: config.kind || null,
            label: config.label || 'Media',
            limits: config.limits || {},
            listUrl: config.listUrl,
            storeUrl: config.storeUrl,
            indexUrl: config.indexUrl || '',

            open: false,
            tab: 'library',
            items: [],
            loading: false,
            search: '',

            mediaId: '',
            selectedUrl: '',
            selectedName: '',

            busy: false,
            phase: 'idle',
            progress: 0,
            progressVisible: false,
            sent: 0,
            total: 0,
            uploadingName: '',
            error: '',
            xhr: null,

            get fieldInputName() {
                return this.fieldExpr ? this.fieldExpr : this.field;
            },

            get mediaIdInputName() {
                if (this.mediaIdFieldExpr) {
                    return this.mediaIdFieldExpr;
                }

                const name = this.fieldInputName;
                const close = name.lastIndexOf(']');

                if (close === name.length - 1) {
                    return name.slice(0, close) + '_media_id]';
                }

                return name + '_media_id';
            },

            get acceptForKind() {
                if (this.kind === 'image') return 'image/jpeg,image/png,image/webp,image/gif';
                if (this.kind === 'video') return 'video/mp4,video/webm,video/quicktime,video/ogg,video/x-m4v,video/x-matroska,video/x-msvideo,video/mpeg,video/3gpp,video/mp2t,video/x-flv,video/x-ms-wmv';
                if (this.kind === 'audio') return 'audio/mpeg,audio/wav,audio/ogg,audio/mp4,audio/aac,audio/x-m4a,audio/flac';
                return this.limits.accept || '';
            },

            get detailText() {
                if (!this.progressVisible || !this.total) return 'Starting upload…';
                return this.formatSize(this.sent) + ' of ' + this.formatSize(this.total);
            },

            openPicker() {
                this.open = true;
                this.error = '';
                if (this.items.length === 0) this.load();
            },

            close() {
                this.open = false;
            },

            async load() {
                this.loading = true;
                try {
                    const params = new URLSearchParams();
                    if (this.kind) params.set('kind', this.kind);
                    if (this.search) params.set('q', this.search);

                    const response = await fetch(this.listUrl + '?' + params.toString(), {
                        headers: { Accept: 'application/json' },
                    });
                    const data = await response.json();
                    this.items = data.items || [];
                } catch (e) {
                    this.error = 'The media library could not be loaded.';
                } finally {
                    this.loading = false;
                }
            },

            choose(item) {
                this.mediaId = String(item.id);
                this.selectedUrl = item.url;
                this.selectedName = item.name;
                this.clearFileInput();
                this.error = '';
                this.open = false;
            },

            clearPick() {
                this.mediaId = '';
                this.selectedUrl = '';
                this.selectedName = '';
                this.error = '';
            },

            /**
             * Dropping a file on the inline "Upload from PC" button uploads it
             * straight into the library and picks it in one step.
             */
            uploadLocal(event) {
                const input = event.target;
                const file = input.files && input.files[0];
                if (!file) return;

                if (this.kind && !this.matchesKind(file.type)) {
                    this.error = 'That is not a ' + this.kind + ' file.';
                    input.value = '';
                    return;
                }

                if (this.limits.maxBytes && file.size > this.limits.maxBytes) {
                    this.error = 'That file is ' + this.formatSize(file.size)
                        + '. The limit is ' + this.formatSize(this.limits.maxBytes) + '.';
                    input.value = '';
                    return;
                }

                this.send(file, () => {
                    input.value = '';
                });
            },

            matchesKind(type) {
                if (!type) return true;
                if (this.kind === 'image') return type.startsWith('image/');
                if (this.kind === 'video') return type.startsWith('video/');
                if (this.kind === 'audio') return type.startsWith('audio/');
                return true;
            },

            send(file, done) {
                this.busy = true;
                this.phase = 'uploading';
                this.progress = 0;
                this.progressVisible = false;
                this.sent = 0;
                this.total = 0;
                this.uploadingName = file.name;
                this.error = '';

                const form = new FormData();
                form.append('file', file);

                const xhr = new XMLHttpRequest();
                this.xhr = xhr;
                xhr.open('POST', this.storeUrl, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);

                // 100% only means the bytes left the browser; the server still
                // has to store the file and write the library row.
                xhr.upload.addEventListener('progress', (e) => {
                    if (!e.lengthComputable) return;
                    this.progressVisible = true;
                    this.sent = e.loaded;
                    this.total = e.total;
                    this.progress = Math.min(99, Math.round((e.loaded / e.total) * 100));
                    if (e.loaded >= e.total) {
                        this.phase = 'processing';
                        this.progress = 99;
                    }
                });

                xhr.addEventListener('load', () => {
                    let data = null;
                    try { data = JSON.parse(xhr.responseText); } catch (e) { data = null; }

                    if (!data) {
                        this.fail('The server did not answer as expected. Check that you are still logged in.');
                        return;
                    }

                    if (xhr.status >= 200 && xhr.status < 300 && data.ok) {
                        this.mediaId = String(data.media.id);
                        this.selectedUrl = data.media.url;
                        this.selectedName = data.media.name;
                        this.open = false;
                        this.busy = false;
                        this.phase = 'idle';
                        this.items = [];
                        if (typeof done === 'function') done();
                        return;
                    }

                    this.fail(this.firstError(data));
                });

                xhr.addEventListener('error', () => this.fail('The upload was interrupted. Check your connection.'));
                xhr.addEventListener('abort', () => this.fail('The upload was cancelled.'));

                xhr.send(form);
            },

            firstError(data) {
                if (data && data.message) return data.message;
                if (data && data.errors) {
                    const first = Object.values(data.errors)[0];
                    if (Array.isArray(first) && first.length) return first[0];
                }
                return 'The upload failed.';
            },

            fail(message) {
                this.busy = false;
                this.phase = 'idle';
                this.progress = 0;
                this.progressVisible = false;
                this.error = message;
            },

            /**
             * A library pick and a freshly chosen file would both be submitted
             * and could disagree, so picking clears the file input.
             */
            clearFileInput() {
                const input = this.$root.querySelector('input[type="file"]');
                if (input) input.value = '';
            },

            formatSize(bytes) {
                if (!bytes) return '0 B';
                if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
                return Math.max(1, Math.round(bytes / 1024)) + ' KB';
            },
        };
    }
</script>