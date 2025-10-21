<?php

declare(strict_types=1);

namespace App\Domains\User\Models;

use Carbon\Carbon;
use Laravel\Sanctum\HasApiTokens;
use App\Domains\Goals\Models\Goal;
use Spatie\Activitylog\LogOptions;
use Database\Factories\UserFactory;
use Spatie\Permission\Traits\HasRoles;
use App\Domains\Planning\Models\Session;
use Illuminate\Notifications\Notifiable;
use App\Domains\Planning\Models\Template;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Доменная модель пользователя системы
 *
 * @property int         $id
 * @property string      $name
 * @property string      $email
 * @property string      $password
 * @property array       $preferences Пользовательские настройки
 * @property string      $timezone    Часовой пояс пользователя
 * @property Carbon      $email_verified_at
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon|null $deleted_at
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'preferences',
        'timezone',
        'total_sessions',
        'total_practice_minutes',
        'last_practice_date',
        'description',
        'title',
        'type',
        'target',
        'end_date',
        'start_date',
        'is_completed',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'preferences'       => 'array',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }

    /**
     * Получить все цели пользователя
     */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    /**
     * Получить предпочтения пользователя с значениями по умолчанию
     */
    public function getPreference(string $key, mixed $default = null): mixed
    {
        return data_get($this->preferences, $key, $default);
    }

    /**
     * Установить предпочтение пользователя
     */
    public function setPreference(string $key, mixed $value): self
    {
        $preferences = $this->preferences ?? [];
        data_set($preferences, $key, $value);

        $this->preferences = $preferences;

        return $this;
    }

    /**
     * Получить часовой пояс пользователя или системный по умолчанию
     */
    public function getTimezoneAttribute($value): string
    {
        return $value ?? config('app.timezone');
    }

    /**
     * Проверить, является ли пользователь активным
     */
    public function isActive(): bool
    {
        return $this->deleted_at === null && $this->email_verified_at !== null;
    }

    /**
     * Получить статистику активности пользователя
     */
    public function getActivityStats(): array
    {
        return [
            'total_sessions'      => $this->sessions()->count(),
            'total_practice_time' => $this->sessions()->sum('actual_duration'),
            'active_templates'    => $this->templates()->count(),
        ];
    }

    /**
     * Получить все сессии занятий пользователя
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Получить все шаблоны занятий пользователя
     */
    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }

    /**
     * Настройки логирования активности
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'preferences'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
