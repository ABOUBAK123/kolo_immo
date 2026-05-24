<?php

namespace App\Helpers;

class WestAfrica
{
    /** All 16 ECOWAS + Mauritania countries with their main capital city */
    public static function countries(): array
    {
        return [
            'Bénin'         => 'Porto-Novo',
            'Burkina Faso'  => 'Ouagadougou',
            'Cap-Vert'      => 'Praia',
            "Côte d'Ivoire" => 'Abidjan',
            'Gambie'        => 'Banjul',
            'Ghana'         => 'Accra',
            'Guinée'        => 'Conakry',
            'Guinée-Bissau' => 'Bissau',
            'Libéria'       => 'Monrovia',
            'Mali'          => 'Bamako',
            'Mauritanie'    => 'Nouakchott',
            'Niger'         => 'Niamey',
            'Nigeria'       => 'Abuja',
            'Sénégal'       => 'Dakar',
            'Sierra Leone'  => 'Freetown',
            'Togo'          => 'Lomé',
        ];
    }

    /** Map legacy ISO-2 codes to country names (backward compat with old DB entries) */
    public static function codeToName(): array
    {
        return [
            'BJ' => 'Bénin',         'BF' => 'Burkina Faso',  'CV' => 'Cap-Vert',
            'CI' => "Côte d'Ivoire", 'GM' => 'Gambie',         'GH' => 'Ghana',
            'GN' => 'Guinée',        'GW' => 'Guinée-Bissau',  'LR' => 'Libéria',
            'ML' => 'Mali',          'MR' => 'Mauritanie',      'NE' => 'Niger',
            'NG' => 'Nigeria',       'SN' => 'Sénégal',         'SL' => 'Sierra Leone',
            'TG' => 'Togo',
        ];
    }

    /** Resolve a stored value (code or full name) to a full country name */
    public static function resolve(string $value): string
    {
        return static::codeToName()[$value] ?? $value;
    }
}
