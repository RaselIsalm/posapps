@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Backup
@endsection

@section('content')
@component('backend.layout.partials.bredcrumbs')
    @slot('title') Backup @endslot
    @slot('subtitle') Save a copy of your sales, stock and uploaded files. You choose how often, and whether it stays here, on another server, or both. @endslot
    <li class="breadcrumb-item">Settings</li>
    <li class="breadcrumb-item active">Backup</li>
@endcomponent

<div class="container-fluid default-dashboard">
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Automatic backup</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.backupUpdate') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="backup_enabled" value="0">
                            <input class="form-check-input" type="checkbox" name="backup_enabled" id="backupEnabled" value="1" {{ old('backup_enabled', $settings['backup_enabled']) === '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="backupEnabled">Run backups automatically</label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="backupInterval">How often</label>
                            <select class="form-select" name="backup_interval" id="backupInterval">
                                @foreach ($intervals as $value => $label)
                                    <option value="{{ $value }}" {{ old('backup_interval', $settings['backup_interval']) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="alert alert-warning mt-2 mb-0 {{ in_array(old('backup_interval', $settings['backup_interval']), ['1min', '5min'], true) ? '' : 'd-none' }}" id="backupTestWarning">
                                1 minute and 5 minutes are only for testing that a backup really runs. After the test, switch to hourly or daily so the disk does not fill up.
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3" id="backupTimeWrap">
                                <label class="form-label" for="backupTime">Time</label>
                                <input type="time" class="form-control" name="backup_daily_time" id="backupTime" value="{{ old('backup_daily_time', $settings['backup_daily_time']) }}">
                            </div>
                            <div class="col-md-6 mb-3 d-none" id="backupDayWrap">
                                <label class="form-label" for="backupDay">Day</label>
                                <select class="form-select" name="backup_weekly_day" id="backupDay">
                                    @foreach ($weekdays as $value => $label)
                                        <option value="{{ $value }}" {{ (string) old('backup_weekly_day', $settings['backup_weekly_day']) === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="backupKeep">How many backups to keep</label>
                            <input type="number" min="3" max="30" class="form-control" name="backup_keep_count" id="backupKeep" value="{{ old('backup_keep_count', $settings['backup_keep_count']) }}">
                            <div class="form-text">Older backups are deleted automatically. 10 is a safe default.</div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="backup_include_files" value="0">
                            <input class="form-check-input" type="checkbox" name="backup_include_files" id="backupFiles" value="1" {{ old('backup_include_files', $settings['backup_include_files']) === '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="backupFiles">Include uploaded files (product photos, logo)</label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="backup_notify" value="0">
                            <input class="form-check-input" type="checkbox" name="backup_notify" id="backupNotify" value="1" {{ old('backup_notify', $settings['backup_notify']) === '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="backupNotify">Email me when a backup finishes or fails</label>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="backupEmail">Notification email</label>
                            <input type="email" class="form-control" name="backup_notify_email" id="backupEmail" value="{{ old('backup_notify_email', $settings['backup_notify_email']) }}" placeholder="owner@example.com">
                            <div class="form-text">The email does not contain the backup file. Mail Settings must already be working.</div>
                        </div>

                        <hr>
                        <h6 class="mb-1">Where to store</h6>
                        <p class="text-muted mb-3">Choose this server, another server, or both. Another server uses FTP.</p>

                        <div class="mb-3">
                            <label class="form-label" for="backupStorage">Store backups</label>
                            <select class="form-select" name="backup_storage" id="backupStorage">
                                <option value="local" {{ old('backup_storage', $settings['backup_storage']) === 'local' ? 'selected' : '' }}>This server only</option>
                                <option value="both" {{ old('backup_storage', $settings['backup_storage']) === 'both' ? 'selected' : '' }}>This server and another server</option>
                                <option value="remote" {{ old('backup_storage', $settings['backup_storage']) === 'remote' ? 'selected' : '' }}>Another server only</option>
                            </select>
                            <div class="form-text" id="backupStorageHelp">The zip stays on this server. Download it from the list below.</div>
                        </div>

                        <div id="backupRemoteFields">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label" for="backupRemoteHost">FTP host</label>
                                <input type="text" class="form-control" name="backup_remote_host" id="backupRemoteHost" value="{{ old('backup_remote_host', $settings['backup_remote_host']) }}" placeholder="ftp.example.com" autocomplete="off">
                                @error('backup_remote_host')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label" for="backupRemotePort">Port</label>
                                <input type="number" class="form-control" name="backup_remote_port" id="backupRemotePort" value="{{ old('backup_remote_port', $settings['backup_remote_port']) }}" min="1" max="65535">
                                @error('backup_remote_port')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="backupRemoteUser">FTP username</label>
                                <input type="text" class="form-control" name="backup_remote_username" id="backupRemoteUser" value="{{ old('backup_remote_username', $settings['backup_remote_username']) }}" autocomplete="off">
                                @error('backup_remote_username')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="backupRemotePass">FTP password</label>
                                <input type="password" class="form-control" name="backup_remote_password" id="backupRemotePass" value="" autocomplete="new-password" placeholder="{{ $settings['backup_remote_password_set'] ? 'Saved. Leave blank to keep it.' : '' }}">
                                @error('backup_remote_password')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="backupRemotePath">Folder</label>
                            <input type="text" class="form-control" name="backup_remote_path" id="backupRemotePath" value="{{ old('backup_remote_path', $settings['backup_remote_path']) }}" placeholder="private-backups">
                            <div class="form-text">Do not use public_html. The host, username, password and folder are encrypted in the database.</div>
                            @error('backup_remote_path')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        </div>

                        <button type="submit" class="btn btn-primary px-4">Save settings</button>
                        <button type="submit" class="btn btn-outline-secondary px-4 ms-2" id="backupRemoteTest" formaction="{{ route('settings.backupRemoteTest') }}">Test connection</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Backup now</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">{{ $nextRun }}</p>
                    <form action="{{ route('settings.backupRun') }}" method="POST" id="backupNowForm">
                        @csrf
                        <button type="submit" class="btn btn-primary px-4" id="backupNowBtn">
                            <i class="fas fa-database me-1"></i> Backup now
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Where the file is kept</h5>
                </div>
                <div class="card-body">
                    @if ($settings['backup_storage'] === 'remote')
                        <p class="mb-2">Each backup is sent to the other server and is not kept here. Download still works from the list. The file is fetched when you ask for it.</p>
                    @elseif ($settings['backup_storage'] === 'both')
                        <p class="mb-2">Each backup stays on this server, outside the public website, and a copy is sent to the other server.</p>
                    @else
                        <p class="mb-2">Each backup stays on this server, outside the public website. Download it from the list below while you are signed in.</p>
                    @endif
                    @if ($encryption)
                        <p class="mb-2">The zip is encrypted. Save this password somewhere that is not this server. You need it to open a downloaded file.</p>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" id="backupPassword" value="{{ $password }}" readonly>
                            <button type="button" class="btn btn-outline-secondary" id="copyPasswordBtn">Copy</button>
                        </div>
                        <form action="{{ route('settings.backupPassword') }}" method="POST" id="newPasswordForm">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm">Generate a new password</button>
                        </form>
                    @else
                        <p class="mb-0 text-muted">This server cannot encrypt zip files. Files stay private on the server, and only an admin can download them.</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Server scheduler</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">Set this once on the server. After that, changing the time on this page is enough.</p>
                    <label class="form-label" for="backupCronCommand">Cron command</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="backupCronCommand" value="{{ $cronCommand }}" readonly>
                        <button type="button" class="btn btn-outline-secondary" id="copyCronBtn">Copy</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5 class="mb-0">Saved backups</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($runs as $run)
                            <tr>
                                <td>{{ optional($run->finished_at ?: $run->started_at)->format('d M Y, h:i A') }}</td>
                                <td>{{ $run->triggerLabel() }}</td>
                                <td>{{ $run->sizeLabel() }}</td>
                                <td>
                                    @if ($run->status === 'success')
                                        <span class="badge bg-success">Saved</span>
                                    @elseif ($run->status === 'failed')
                                        <span class="badge bg-danger">Failed</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Running</span>
                                    @endif
                                    @if ($run->message && ($run->status === 'failed' || str_contains($run->message, 'other server')))
                                        <div class="small text-muted">{{ $run->message }}</div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if ($run->status === 'success' && $run->filename)
                                        <a href="{{ route('settings.backupDownload', $run) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary restore-backup-btn" data-action="{{ route('settings.backupRestore', $run) }}">Restore</button>
                                        <form action="{{ route('settings.backupDelete', $run) }}" method="POST" class="d-inline backup-delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No backups yet. Use Backup now to create the first one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="restoreBackupModal" tabindex="-1" aria-labelledby="restoreBackupTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST" id="restoreBackupForm">
                @csrf
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold" id="restoreBackupTitle"><i class="fas fa-undo me-2"></i>Restore this backup?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">Current sales, stock and customer data will be replaced by this backup. A safety copy is saved first.</p>
                    <label class="form-label" for="restoreConfirmInput">Type RESTORE to confirm</label>
                    <input type="text" name="confirm" id="restoreConfirmInput" class="form-control" placeholder="RESTORE" autocomplete="off">
                    <div class="d-flex justify-content-end mt-3">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Discard</button>
                        <button type="submit" class="btn btn-danger" id="restoreConfirmBtn" disabled>Restore</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var interval = document.getElementById('backupInterval');
        var warning = document.getElementById('backupTestWarning');
        var timeWrap = document.getElementById('backupTimeWrap');
        var dayWrap = document.getElementById('backupDayWrap');
        var storage = document.getElementById('backupStorage');
        var remoteFields = document.getElementById('backupRemoteFields');
        var remoteTest = document.getElementById('backupRemoteTest');
        var storageHelp = document.getElementById('backupStorageHelp');
        var storageHelpText = {
            local: @json(__('The zip stays on this server. Download it from the list below.')),
            both: @json(__('The zip stays on this server, and the same file is sent to the other server.')),
            remote: @json(__('The zip is sent to the other server and removed from this server. If the upload fails, the file stays here so the backup is not lost.'))
        };

        function syncStorage() {
            var remote = storage.value !== 'local';
            remoteFields.classList.toggle('d-none', !remote);
            remoteTest.classList.toggle('d-none', !remote);
            storageHelp.textContent = storageHelpText[storage.value] || storageHelpText.local;
        }
        storage.addEventListener('change', syncStorage);
        syncStorage();

        function syncInterval() {
            var value = interval.value;
            warning.classList.toggle('d-none', value !== '1min' && value !== '5min');
            var calendar = value === 'daily' || value === 'weekly';
            timeWrap.classList.toggle('d-none', !calendar);
            dayWrap.classList.toggle('d-none', value !== 'weekly');
        }
        interval.addEventListener('change', syncInterval);
        syncInterval();

        var nowForm = document.getElementById('backupNowForm');
        nowForm.addEventListener('submit', function () {
            var btn = document.getElementById('backupNowBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving backup...';
        });

        function bindCopy(buttonId, inputId) {
            var button = document.getElementById(buttonId);
            if (!button) return;
            button.addEventListener('click', function () {
                var input = document.getElementById(inputId);
                navigator.clipboard.writeText(input.value).then(function () {
                    button.textContent = 'Copied';
                });
            });
        }
        bindCopy('copyPasswordBtn', 'backupPassword');
        bindCopy('copyCronBtn', 'backupCronCommand');

        var passwordForm = document.getElementById('newPasswordForm');
        if (passwordForm) {
            passwordForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var form = this;
                window.showAppConfirm({
                    title: @json(__('Generate a new password?')),
                    text: @json(__('New backups will use it. Older files keep the password they were created with.')),
                    icon: 'fas fa-key',
                    confirmText: @json(__('Generate'))
                }).then(function (ok) {
                    if (ok) form.submit();
                });
            });
        }

        document.querySelectorAll('.backup-delete-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                window.showAppConfirm({
                    title: @json(__('Delete this backup?')),
                    text: @json(__('The file will be removed from this server and from the other server, if a copy was sent there.')),
                    confirmText: @json(__('Yes, delete'))
                }).then(function (ok) {
                    if (ok) form.submit();
                });
            });
        });

        var restoreModalEl = document.getElementById('restoreBackupModal');
        var restoreModal = new bootstrap.Modal(restoreModalEl);
        var restoreForm = document.getElementById('restoreBackupForm');
        var restoreInput = document.getElementById('restoreConfirmInput');
        var restoreBtn = document.getElementById('restoreConfirmBtn');
        restoreInput.addEventListener('input', function () {
            restoreBtn.disabled = restoreInput.value.trim().toUpperCase() !== 'RESTORE';
        });
        document.querySelectorAll('.restore-backup-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                restoreForm.action = btn.getAttribute('data-action');
                restoreInput.value = '';
                restoreBtn.disabled = true;
                restoreModal.show();
            });
        });
    });
</script>
@endpush
