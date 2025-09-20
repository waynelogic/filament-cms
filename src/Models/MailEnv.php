<?php

namespace Waynelogic\FilamentCms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Env;

class MailEnv extends Model
{
    protected $guarded = [];
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fill([
            'mailer' => Env::get('MAIL_MAILER'),
            'scheme' => Env::get('MAIL_SCHEME'),
            'host' => Env::get('MAIL_HOST'),
            'port' => Env::get('MAIL_PORT'),
            'username' => Env::get('MAIL_USERNAME'),
            'password' => Env::get('MAIL_PASSWORD'),
            'from_address' => Env::get('MAIL_FROM_ADDRESS'),
            'from_name' => Env::get('MAIL_FROM_NAME'),
        ]);
    }

    public function update(array $attributes = [], array $options = []): bool
    {
        return $this->fill($attributes)->save($options);
    }

    public function save(array $options = []): true
    {
        $this->writeVariable('MAIL_MAILER', $this->mailer->value);
        $this->writeVariable('MAIL_SCHEME', $this->scheme);
        $this->writeVariable('MAIL_HOST', $this->host);
        $this->writeVariable('MAIL_PORT', $this->port);
        $this->writeVariable('MAIL_USERNAME', $this->username);
        $this->writeVariable('MAIL_PASSWORD', $this->password);
        $this->writeVariable('MAIL_FROM_ADDRESS', $this->from_address);
        $this->writeVariable('MAIL_FROM_NAME', $this->from_name);

        return true;
    }

    private function writeVariable(string $key, string $value = null): void
    {
        $path = base_path('.env');

        Env::writeVariable($key, $value, $path, true);
    }
}
