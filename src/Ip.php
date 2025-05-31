<?php declare(strict_types=1);

namespace Asterios\Core;

class Ip
{
    /**
     * IPV4 in range
     *
     * 1. Wildcard format:     1.2.3.*
     * 2. CIDR format:         1.2.3/24  OR  1.2.3.4/255.255.255.0
     * 3. Start-End IP format: 1.2.3.0-1.2.3.255
     */
    public function ipv4_in_range(string $ip, string $range): bool
    {
        if (false !== strpos($range, '/'))
        {
            // Range is in IP/NETMASK format
            [$range, $netmask] = explode('/', $range, 2);

            if (false !== strpos($netmask, '.'))
            {
                // netmask is a 255.255.0.0 format
                $netmask = str_replace('*', '0', $netmask);
                $netmask_dec = ip2long($netmask);

                return ((ip2long($ip) & $netmask_dec) === (ip2long($range) & $netmask_dec));
            }

            // netmask is a CIDR size block; fix the range argument
            $x = explode('.', $range);

            while (count($x) < 4)
            {
                $x[] = '0';
            }

            [$a, $b, $c, $d] = $x;

            $range = sprintf('%u.%u.%u.%u', empty($a) ? '0' : $a, empty($b) ? '0' : $b, empty($c) ? '0' : $c, empty($d) ? '0' : $d);
            $range_dec = ip2long($range);
            $ip_dec = ip2long($ip);

            $wildcard_dec = (2 ** (32 - (int)$netmask)) - 1;
            $netmask_dec = ~$wildcard_dec;

            return (($ip_dec & $netmask_dec) === ($range_dec & $netmask_dec));
        }

        // range might be 255.255.*.* or 1.2.3.0-1.2.3.255
        if (false !== strpos($range, '*'))
        {
            $lower = str_replace('*', '0', $range);
            $upper = str_replace('*', '255', $range);
            $range = $lower . '-' . $upper;
        }

        if (false !== strpos($range, '-'))
        {
            [$lower, $upper] = explode('-', $range, 2);

            $lower_dec = (float)sprintf('%u', ip2long($lower));
            $upper_dec = (float)sprintf('%u', ip2long($upper));
            $ip_dec = (float)sprintf('%u', ip2long($ip));

            return (($ip_dec >= $lower_dec) && ($ip_dec <= $upper_dec));
        }

        return false;
    }

    public function ip2long6(string $ip): string
    {
        if (substr_count($ip, '::'))
        {
            $ip = str_replace('::', str_repeat(':0000', 8 - substr_count($ip, ':')) . ':', $ip);
        }

        $exploded_ip = explode(':', $ip);
        $r_ip = '';

        foreach ($exploded_ip as $v)
        {
            $r_ip .= str_pad(base_convert($v, 16, 2), 16, '0', STR_PAD_LEFT);
        }

        return base_convert($r_ip, 2, 10);
    }

    public function get_ipv6_full(string $ip): string
    {
        $pieces = $this->ip_to_array($ip);

        [$main_ip_piece, $last_ip_piece] = explode('::', $pieces[0], 2);

        $main_ip_pieces = explode(':', $main_ip_piece);

        foreach ($main_ip_pieces as $key => $value)
        {
            $main_ip_pieces[$key] = str_pad($value, 4, '0', STR_PAD_LEFT);
        }

        $total_pieces = count($main_ip_pieces);

        if ('' !== trim($last_ip_piece))
        {
            $last_piece = str_pad($last_ip_piece, 4, '0', STR_PAD_LEFT);

            for ($i = $total_pieces; $i < 7; $i++)
            {
                $main_ip_pieces[$i] = '0000';
            }

            $main_ip_pieces[7] = $last_piece;
        }
        else
        {
            for ($i = $total_pieces; $i < 8; $i++)
            {
                $main_ip_pieces[$i] = '0000';
            }
        }

        // Rebuild the final long form IPV6 address
        $final_ip = implode(':', $main_ip_pieces);

        return $this->ip2long6($final_ip);
    }

    public function ipv6_in_range(string $ip, string $range_ip): bool
    {
        $pieces = $this->ip_to_array($range_ip);

        [$main_ip_piece, $last_ip_piece] = explode('::', $pieces[0], 2);

        $main_ip_pieces = explode(':', $main_ip_piece);

        foreach ($main_ip_pieces as $key => $value)
        {
            $main_ip_pieces[$key] = str_pad($value, 4, '0', STR_PAD_LEFT);
        }

        $first = $main_ip_pieces;
        $last = $main_ip_pieces;

        $total_pieces = count($main_ip_pieces);

        if ('' !== trim($last_ip_piece))
        {
            $last_piece = str_pad($last_ip_piece, 4, '0', STR_PAD_LEFT);

            for ($i = $total_pieces; $i < 7; $i++)
            {
                $first[$i] = '0000';
                $last[$i] = 'ffff';
            }

            $main_ip_pieces[7] = $last_piece;
        }
        else
        {
            for ($i = $total_pieces; $i < 8; $i++)
            {
                $first[$i] = '0000';
                $last[$i] = 'ffff';
            }
        }

        // Rebuild the final long form IPV6 address
        $first = $this->ip2long6(implode(':', $first));
        $last = $this->ip2long6(implode(':', $last));

        return ($ip >= $first && $ip <= $last);
    }

    private function ip_to_array(string $ip): array
    {
        return explode('/', $ip, 2);
    }
}
