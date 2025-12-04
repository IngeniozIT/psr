<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message;

final readonly class UriPort
{
    public const DEFAULTS = [
        // Web
        'http' => 80,
        'https' => 443,
        'ws' => 80,
        'wss' => 443,

        // File Transfer
        'ftp' => 21,
        'sftp' => 22,
        'ssh' => 22,
        'rsync' => 873,
        'svn' => 3690,
        'git' => 9418,

        // Email
        'imap' => 143,
        'pop' => 110,

        // Directory / Lookup
        'ldap' => 389,
        'ldaps' => 636,
        'acap' => 674,
        'dict' => 2628,
        'dns' => 53,
        'wais' => 210,

        // Messaging / Chat
        'irc' => 194,
        'ircs' => 6697,
        'nntp' => 119,
        'nntps' => 563,

        // Media / Streaming
        'rtsp' => 554,
        'rtsps' => 322,
        'rtspu' => 5005,
        'mms' => 1755,

        // Other
        'afp' => 548,
        'gopher' => 70,
        'ipp' => 631,
        'ipps' => 631,
        'msrp' => 2855,
        'mtqp' => 1038,
        'nfs' => 111,
        'prospero' => 1525,
        'redis' => 6379,
        'smb' => 445,
        'snmp' => 161,
        'telnet' => 23,
        'vnc' => 5900,
        'ventrilo' => 3784,
    ];

    public static function isDefault(string $scheme, ?int $port): bool
    {
        return (self::DEFAULTS[$scheme] ?? null) === $port;
    }
}
