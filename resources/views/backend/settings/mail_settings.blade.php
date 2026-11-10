@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Mail Settings
@endsection

@push('css')
    <style>
        .mail-info-box {
            background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%);
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 20px;
        }
        .mail-info-box h6 {
            color: #1e40af;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .mail-info-box ol {
            color: #374151;
            font-size: 13px;
            margin-bottom: 0;
            padding-left: 18px;
        }
        .mail-info-box ol li {
            margin-bottom: 4px;
        }
        .mail-info-box a {
            color: #4f46e5;
            font-weight: 500;
        }
        .test-mail-section {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
        }
        .advanced-toggle {
            cursor: pointer;
            color: #6b7280;
            font-size: 13px;
            user-select: none;
            transition: color 0.2s;
        }
        .advanced-toggle:hover {
            color: #4f46e5;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-badge.active {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-badge.inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')Mail Settings @endslot
        @slot('subtitle')Configure your email (Gmail + App Password). @endslot
        <li class="breadcrumb-item">Settings</li>
        <li class="breadcrumb-item active">Mail Settings</li>
    @endcomponent

    <div class="container-fluid default-dashboard">
        <div class="row">
            {{-- Mail Configuration Form --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Mail Configuration</h5>
                        @if($mailSetting && $mailSetting->is_active)
                            <span class="status-badge active"><i class="fas fa-check-circle"></i> Active</span>
                        @else
                            <span class="status-badge inactive"><i class="fas fa-times-circle"></i> Inactive</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <form action="{{ route('mail-settings.store') }}" method="POST" id="mailSettingsForm">
                            @csrf

                            {{-- Gmail Credentials --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mail_username" class="form-label">Gmail Address <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-at"></i></span>
                                            <input type="email" class="form-control" id="mail_username" name="mail_username"
                                                   value="{{ old('mail_username', $mailSetting->mail_username ?? '') }}"
                                                   placeholder="your-email@gmail.com" required>
                                        </div>
                                        @error('mail_username')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mail_password" class="form-label">App Password <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                                            <input type="password" class="form-control" id="mail_password" name="mail_password"
                                                   value="{{ $mailSetting ? '••••••••••••••••' : old('mail_password', '') }}"
                                                   placeholder="16-character app password" required>
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword" title="Show/Hide">
                                                <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                            </button>
                                        </div>
                                        @error('mail_password')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- From Details --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mail_from_name" class="form-label">From Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="mail_from_name" name="mail_from_name"
                                               value="{{ old('mail_from_name', $mailSetting->mail_from_name ?? ($general_settings['company_name'] ?? 'OMS')) }}"
                                               placeholder="Company Name" required>
                                        @error('mail_from_name')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mail_from_address" class="form-label">From Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="mail_from_address" name="mail_from_address"
                                               value="{{ old('mail_from_address', $mailSetting->mail_from_address ?? '') }}"
                                               placeholder="your-email@gmail.com" required>
                                        @error('mail_from_address')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Active Toggle --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <label class="form-check-label mb-0" for="is_active">
                                            <strong>Enable Mail Service</strong>
                                        </label>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="is_active"
                                                   name="is_active" style="width: 45px; height: 22px;"
                                                   {{ ($mailSetting && $mailSetting->is_active) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Advanced Settings (Collapsible) --}}
                            <div class="mb-3">
                                <div class="advanced-toggle" data-bs-toggle="collapse" data-bs-target="#advancedSettings">
                                    <i class="fas fa-cog me-1"></i> Advanced Settings
                                    <i class="fas fa-chevron-down ms-1" style="font-size: 10px;"></i>
                                </div>
                                <div class="collapse {{ $mailSetting && ($mailSetting->mail_host !== 'smtp.gmail.com' || $mailSetting->mail_port !== 587) ? 'show' : '' }}" id="advancedSettings">
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="mail_host" class="form-label">Mail Host</label>
                                                <input type="text" class="form-control" id="mail_host" name="mail_host"
                                                       value="{{ old('mail_host', $mailSetting->mail_host ?? 'smtp.gmail.com') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="mail_port" class="form-label">Mail Port</label>
                                                <input type="number" class="form-control" id="mail_port" name="mail_port"
                                                       value="{{ old('mail_port', $mailSetting->mail_port ?? 587) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="mail_encryption" class="form-label">Encryption</label>
                                                <select class="form-control" id="mail_encryption" name="mail_encryption">
                                                    <option value="tls" {{ old('mail_encryption', $mailSetting->mail_encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                                    <option value="ssl" {{ old('mail_encryption', $mailSetting->mail_encryption ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                                    <option value="null" {{ old('mail_encryption', $mailSetting->mail_encryption ?? '') === 'null' ? 'selected' : '' }}>None</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Test Mail Section --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Send Test Mail</h5>
                    </div>
                    <div class="card-body">
                        <div class="test-mail-section">
                            @if($mailSetting && $mailSetting->is_active)
                                <div class="row align-items-end">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="test_email" class="form-label">Recipient Email Address</label>
                                            <input type="email" class="form-control" id="test_email"
                                                   placeholder="test@example.com">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-success w-100" id="sendTestMailBtn">
                                            <span class="spinner-border spinner-border-sm d-none me-1" id="testMailSpinner" role="status"></span>
                                            <i class="fas fa-paper-plane me-1" id="testMailIcon"></i>
                                            <span id="testMailBtnText">Send Test Mail</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2 d-none" id="testMailResult">
                                    <div class="alert mb-0" id="testMailAlert"></div>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-exclamation-triangle text-warning" style="font-size: 24px;"></i>
                                    <p class="mt-2 mb-0 text-muted">
                                        To send a test mail, please save the settings first and enable the "Enable Mail Service" toggle.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar: Instructions --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Create Gmail App Password</h5>
                    </div>
                    <div class="card-body">
                        <div class="mail-info-box">
                            <h6><i class="fas fa-shield-alt me-1"></i> Step-by-step Guide:</h6>
                            <ol>
                                <li>Go to your Google Account</li>
                                <li>Enable <strong>Security</strong> → <strong>2-Step Verification</strong></li>
                                <li>Visit the <a href="https://myaccount.google.com/apppasswords" target="_blank">App Passwords</a> page</li>
                                <li>Enter an App name (e.g., "OMS")</li>
                                <li>Click <strong>Generate</strong></li>
                                <li>Copy the 16-character password</li>
                                <li>Paste it here in the "App Password" field</li>
                            </ol>
                        </div>

                        <div class="alert alert-warning mb-3" role="alert" style="font-size: 13px;">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <strong>Important:</strong> Do not use your actual Gmail password. Use the generated App Password only.
                        </div>

                        <div class="alert alert-info mb-0" role="alert" style="font-size: 13px;">
                            <i class="fas fa-lock me-1"></i>
                            <strong>Security:</strong> Your credentials are encrypted and stored safely in the database. No one can see the plain text.
                        </div>
                    </div>
                </div>

                {{-- Quick Settings Reference --}}
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-server me-2"></i>Common SMTP Settings</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th>Provider</th>
                                    <th>Host</th>
                                    <th>Port</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Gmail</strong></td>
                                    <td>smtp.gmail.com</td>
                                    <td>587 (TLS)</td>
                                </tr>
                                <tr>
                                    <td>Outlook</td>
                                    <td>smtp.office365.com</td>
                                    <td>587 (TLS)</td>
                                </tr>
                                <tr>
                                    <td>Yahoo</td>
                                    <td>smtp.mail.yahoo.com</td>
                                    <td>587 (TLS)</td>
                                </tr>
                                <tr>
                                    <td>Zoho</td>
                                    <td>smtp.zoho.com</td>
                                    <td>587 (TLS)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('mail_password');
            const icon = document.getElementById('togglePasswordIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Auto-fill From Email when Gmail changes
        document.getElementById('mail_username').addEventListener('input', function () {
            const fromEmail = document.getElementById('mail_from_address');
            if (!fromEmail.value || fromEmail.value === this.defaultValue) {
                fromEmail.value = this.value;
            }
        });

        // Clear masked password on focus if it's the placeholder
        document.getElementById('mail_password').addEventListener('focus', function () {
            if (this.value === '••••••••••••••••') {
                this.value = '';
                this.type = 'text';
                document.getElementById('togglePasswordIcon').classList.remove('fa-eye');
                document.getElementById('togglePasswordIcon').classList.add('fa-eye-slash');
            }
        });

        // Restore masked password on blur if empty
        document.getElementById('mail_password').addEventListener('blur', function () {
            if (this.value === '' && '{{ $mailSetting ? "yes" : "" }}') {
                this.value = '••••••••••••••••';
                this.type = 'password';
                document.getElementById('togglePasswordIcon').classList.remove('fa-eye-slash');
                document.getElementById('togglePasswordIcon').classList.add('fa-eye');
            }
        });

        // Send Test Mail (AJAX)
        const testMailBtn = document.getElementById('sendTestMailBtn');
        if (testMailBtn) {
            testMailBtn.addEventListener('click', function () {
                const email = document.getElementById('test_email').value;
                if (!email) {
                    showAppToast(@json(__('Please enter a test email address.')), 'warning');
                    return;
                }

                const spinner = document.getElementById('testMailSpinner');
                const icon = document.getElementById('testMailIcon');
                const btnText = document.getElementById('testMailBtnText');
                const resultDiv = document.getElementById('testMailResult');
                const alertDiv = document.getElementById('testMailAlert');

                // Show loading state
                spinner.classList.remove('d-none');
                icon.classList.add('d-none');
                btnText.textContent = 'Sending...';
                testMailBtn.disabled = true;
                resultDiv.classList.add('d-none');

                fetch('{{ route("mail-settings.test") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ test_email: email }),
                })
                .then(response => response.json())
                .then(data => {
                    resultDiv.classList.remove('d-none');
                    alertDiv.className = 'alert mb-0 ' + (data.success ? 'alert-success' : 'alert-danger');
                    alertDiv.innerHTML = (data.success ? '<i class="fas fa-check-circle me-1"></i>' : '<i class="fas fa-times-circle me-1"></i>') + data.message;
                })
                .catch(error => {
                    resultDiv.classList.remove('d-none');
                    alertDiv.className = 'alert mb-0 alert-danger';
                    alertDiv.innerHTML = '<i class="fas fa-times-circle me-1"></i> ' + @json(__('Request failed')) + ': ' + error.message;
                })
                .finally(() => {
                    spinner.classList.add('d-none');
                    icon.classList.remove('d-none');
                    btnText.textContent = @json(__('Send Test Mail'));
                    testMailBtn.disabled = false;
                });
            });
        }

        // Sidebar menu highlight
        $('#settings').addClass('active');
        $('#settings-submenu').addClass('d-block');
    </script>
@endpush
