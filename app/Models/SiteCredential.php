<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteCredential extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'site_key',
        'credentials',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'credentials' => 'array',
    ];

    /**
     * Get the team that owns the credentials.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get a specific credential value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getCredential(string $key, $default = null)
    {
        return $this->credentials[$key] ?? $default;
    }

    /**
     * Set a specific credential value.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setCredential(string $key, $value)
    {
        $credentials = $this->credentials;
        $credentials[$key] = $value;
        $this->credentials = $credentials;
        
        return $this;
    }

    /**
     * Get the site configuration from the config file.
     *
     * @return array|null
     */
    public function getSiteConfig(): ?array
    {
        return config("site_credentials.sites.{$this->site_key}");
    }

    /**
     * Get the masked credentials for display.
     *
     * @return array
     */
    public function getMaskedCredentials(): array
    {
        $siteConfig = $this->getSiteConfig();
        $maskedCredentials = [];
        
        if (!$siteConfig || !isset($siteConfig['fields'])) {
            return $maskedCredentials;
        }
        
        foreach ($siteConfig['fields'] as $field) {
            $fieldName = $field['name'];
            $value = $this->getCredential($fieldName);
            
            if ($value && $field['type'] === 'password') {
                // Mask password fields with asterisks, but keep the first and last character
                $length = strlen($value);
                if ($length > 2) {
                    $maskedCredentials[$fieldName] = substr($value, 0, 1) . str_repeat('*', $length - 2) . substr($value, -1);
                } else {
                    $maskedCredentials[$fieldName] = str_repeat('*', $length);
                }
            } else {
                $maskedCredentials[$fieldName] = $value;
            }
        }
        
        return $maskedCredentials;
    }
}
