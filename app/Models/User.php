<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    use LogsActivity;

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'username',
        'password',
        'is_active',
        'designation',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logExcept(['password', 'remember_token', 'avatar'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(['updated_at'])
            ->setDescriptionForEvent(fn (string $eventName) => "User has been {$eventName}");
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->properties = $activity->properties->merge([
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (empty($this->avatar)) {
            return null;
        }

        $path = 'users/'.$this->avatar;
        if (! Storage::disk('public')->exists($path)
            && ! is_file(public_path('storage/'.$path))) {
            return null;
        }

        // Root-relative so it works on pos-software.test even if APP_URL is :8000
        return '/storage/'.$path;
    }

    public function hasOperationalHistory(): bool
    {
        $employeeIds = Employee::query()->where('user_id', $this->id)->pluck('id');
        if ($employeeIds->isNotEmpty()) {
            foreach ([
                ['sales', 'seller_id'],
                ['employee_attendance', 'employee_id'],
                ['payrolls', 'employee_id'],
                ['seller_commissions', 'employee_id'],
            ] as [$table, $column]) {
                if ($this->columnExists($table, $column) && DB::table($table)->whereIn($column, $employeeIds)->exists()) {
                    return true;
                }
            }
        }

        foreach ([
            ['purchases', 'created_by'],
            ['sale_returns', 'created_by'],
            ['purchase_returns', 'created_by'],
            ['quotations', 'created_by'],
            ['transfers', 'user_id'],
            ['transactions', 'created_by'],
            ['expenses', 'created_by'],
            ['expenses', 'updated_by'],
            ['gift_cards', 'created_by'],
            ['customers', 'created_by'],
            ['suppliers', 'created_by'],
            ['accounts', 'created_by'],
            ['product_imeis', 'created_by'],
            ['product_imeis', 'updated_by'],
            ['leads', 'created_by'],
            ['lead_notes', 'created_by'],
            ['employees', 'created_by'],
            ['employees', 'updated_by'],
            ['employee_attendance', 'created_by'],
            ['branches', 'manager_id'],
            ['branches', 'created_by'],
        ] as [$table, $column]) {
            if ($this->columnExists($table, $column) && DB::table($table)->where($column, $this->id)->exists()) {
                return true;
            }
        }

        return false;
    }

    public function deactivateAccount(): void
    {
        $this->forceFill(['is_active' => false])->save();

        Employee::query()->where('user_id', $this->id)->whereNull('leaving_date')->update([
            'leaving_date' => now()->toDateString(),
        ]);
        Employee::query()->where('user_id', $this->id)->update(['status' => 0]);
    }

    public function activateAccount(): void
    {
        $this->forceFill(['is_active' => true])->save();

        Employee::query()->where('user_id', $this->id)->update([
            'status' => 1,
            'leaving_date' => null,
        ]);
    }

    private function columnExists(string $table, string $column): bool
    {
        static $cache = [];
        $key = $table.'.'.$column;
        if (! array_key_exists($key, $cache)) {
            $cache[$key] = Schema::hasTable($table) && Schema::hasColumn($table, $column);
        }

        return $cache[$key];
    }
}
