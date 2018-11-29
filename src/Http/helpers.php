<?php

if (! function_exists('setting')) {
    /**
     * @param string $value
     * @param string $default
     * @return mixed|string
     */
    function setting($value, $default = '')
    {
        return Vis\Builder\Setting::get($value, $default);
    }
}

if (! function_exists('settingWithLang')) {
    /**
     * @param string $value
     * @param string $default
     * @return mixed|string
     */
    function settingWithLang($value, $default = '')
    {
        return Vis\Builder\Setting::getWithLang($value, $default);
    }
}

if (! function_exists('dr')) {
    /**
     * @param $array
     */
    function dr($array)
    {
        echo '<pre>';
        die(print_r($array));
    }
}

if (! function_exists('print_arr')) {
    /**
     * @param $array
     */
    function print_arr($array)
    {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }
}

if (! function_exists('remove_bom')) {
    /**
     * @param $val
     * @return bool|string
     */
    function remove_bom($val)
    {
        if (substr($val, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf)) {
            $val = substr($val, 3);
        }

        return $val;
    }
}

if (! function_exists('glide')) {
    /**
     * @param $source
     * @param array $options
     * @return mixed|string
     */
    function glide($source, $options = [])
    {
        if (
            env('IMG_PLACEHOLDER', true)
            && (env('APP_ENV') === 'local' || env('APP_ENV') === 'testing')
        ) {
            $width = $options['w'] ?? 100;
            $height = $options['h'] ?? 100;

            return "//via.placeholder.com/{$width}x{$height}";
        }

        return (new Vis\Builder\Img())->get($source, $options);
    }
}

if (! function_exists('filesize_format')) {
    /**
     * @param $bytes
     * @return string
     */
    function filesize_format($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 1, '.', '').' Gb';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 1, '.', '').' Mb';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 1, '.', '').' Kb';
        } elseif ($bytes > 1) {
            $bytes = $bytes.' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes.' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }
}

/*
 * @param $url
 * @param bool $locale
 * @param array $attributes
 * @return false|string
 */
if (! function_exists('geturl')) {
    function geturl($url, $locale = false, $attributes = [])
    {
        if (! $locale) {
            $locale = App::getLocale();
        }

        return LaravelLocalization::getLocalizedURL($locale, $url, $attributes);
    }
}

/*
 * @param $phrase
 * @return mixed
 */
if (! function_exists('__cms')) {
    function __cms($phrase)
    {
        $thisLang = Cookie::get('lang_admin');

        $arrayTranslate = Vis\TranslationsCMS\Trans::fillCacheTrans();

        return $arrayTranslate[$phrase][$thisLang] ?? $phrase;
    }
}

/*
 * get realy ip user
 *
 * @return string
 */
if (! function_exists('getIp')) {
    function getIp()
    {
        if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
}

/*
 * @param $tree
 * @param $node
 * @param array $slugs
 * @return string
 */
if (! function_exists('recurseMyTree')) {
    function recurseMyTree($tree, $node, &$slugs = [])
    {
        if (! $node['parent_id']) {
            return $node['slug'];
        }

        $slugs[] = $node['slug'];
        $idParent = $node['parent_id'];
        if ($idParent) {
            $parent = $tree[$idParent];
            recurseMyTree($tree, $parent, $slugs);
        }

        return implode('/', array_reverse($slugs));
    }
}

/*
 * Returns entire string with current locale postfix, ex. string_ua
 *
 * @param  string
 * @return string
 */
if (! function_exists('getWithLocalePostfix')) {
    function getWithLocalePostfix($string)
    {
        $currentLocale = LaravelLocalization::getCurrentLocale();

        return $currentLocale == LaravelLocalization::getDefaultLocale() ? $string : $string.'_'.$currentLocale;
    }
}
