<?php
/*
 * server-info.php
 * 
 * Copyright 2019 Jim Richardson <jim@noideersoftware.co.uk>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 *  this holds an array of servers to query
 * now deprecated we have moved to database records to store this
 */
$servers = [
       [
    'id'      => 'fofserver',
    'type'    => 'source',
    'host'    => '46.32.237.232:27015'
    ],
    [
    'id'      => 'mcserver',
    'type'    => 'minecraft',
    'host'    => '46.32.237.232:25565'
    ] 
];
?>

