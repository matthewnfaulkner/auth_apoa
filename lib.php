<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Authentication class for apoa is defined here.
 *
 * @package     auth_apoa
 * @copyright   2022 Matthew<you@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


function is_federation_pending(){
    global $USER;

    $cache = \cache::make('auth_apoa', 'is_federation_pending_cache');
    
    $cachekey = "u_$USER->id";
    if ($data = $cache->get($cachekey)){
        return $data['federation_pending'];
    }
    else{
        if($profile = profile_user_record($USER->id)){
            $federationpending = $profile->federation_pending == 1 ? True : False;
            if($federationpending){
                $cache->set($cachekey, array('federation_pending' => $federationpending));
            }
            return $federationpending;
        }
        
    }
}
