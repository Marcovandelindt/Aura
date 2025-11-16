<?php

namespace App\Enums;

enum SpotifyScope: string
{
    case USER_READ_EMAIL               = 'user-read-email';
    case USER_READ_PRIVATE             = 'user-read-private';

    case USER_READ_PLAYBACK_STATE      = 'user-read-playback-state';
    case USER_MODIFY_PLAYBACK_STATE    = 'user-modify-playback-state';
    case USER_READ_CURRENTLY_PLAYING   = 'user-read-currently-playing';

    case USER_READ_RECENTLY_PLAYED     = 'user-read-recently-played';
    case USER_TOP_READ                 = 'user-top-read';

    case USER_LIBRARY_READ             = 'user-library-read';
    case USER_LIBRARY_MODIFY           = 'user-library-modify';

    case PLAYLIST_READ_PRIVATE         = 'playlist-read-private';
    case PLAYLIST_READ_COLLABORATIVE   = 'playlist-read-collaborative';
    case PLAYLIST_MODIFY_PRIVATE       = 'playlist-modify-private';
    case PLAYLIST_MODIFY_PUBLIC        = 'playlist-modify-public';

    case USER_FOLLOW_READ              = 'user-follow-read';
    case USER_FOLLOW_MODIFY            = 'user-follow-modify';

    case USER_READ_PLAYBACK_POSITION   = 'user-read-playback-position';

    case APP_REMOTE_CONTROL            = 'app-remote-control';
    case STREAMING                     = 'streaming';
}
