<?php

namespace App\Models;

class MailSetting extends BaseModel
{
    protected $fillable = [
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'is_active',
    ];

    protected $casts = [
        'mail_port' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Encrypted attributes — Laravel will auto-encrypt on set, auto-decrypt on get.
     */
    protected $encryptable = ['mail_username', 'mail_password'];

    /**
     * Override getAttribute to decrypt sensitive fields.
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encryptable) && $value !== null) {
            try {
                return decrypt($value);
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }

    /**
     * Override setAttribute to encrypt sensitive fields.
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable) && $value !== null) {
            $value = encrypt($value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Get the active mail setting.
     */
    public static function getActiveSetting(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Build an array compatible with Laravel's mail config.
     */
    public function buildMailerConfig(): array
    {
        return [
            'mail.default' => $this->mail_mailer,
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $this->mail_host,
            'mail.mailers.smtp.port' => $this->mail_port,
            'mail.mailers.smtp.username' => $this->mail_username,
            'mail.mailers.smtp.password' => $this->mail_password,
            'mail.mailers.smtp.encryption' => $this->mail_encryption === 'null' ? null : $this->mail_encryption,
            'mail.from.address' => $this->mail_from_address,
            'mail.from.name' => $this->mail_from_name,
        ];
    }
}
