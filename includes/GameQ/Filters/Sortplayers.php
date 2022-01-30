<?php
/**
 * This file is part of GameQ3.
 *
 * GameQ3 is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * GameQ3 is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 */
 
namespace GameQ\Filters;

use GameQ\Server;

class Sortplayers  extends Base{

	const DEFAULT_ORDER = 'desc';
	 public function __construct(array $options = [])
    {
        // Check for passed keys
        //echo 'in construct<br>';
        
        parent::__construct($options);
        //print_r($options);
    }
 
	public function apply($result, Server $server) {
		
		if (empty($result['players'])) {
			return $result;
		}
		//$args['sortkeys'] 
		$args =$this->options;
		//echo "args => ".print_r($args,true).'<br>';
		$sortkeys = array(
			array('key' => 'score', 'order' => 'desc')
		);
		if (isset($args)) {
			$sortkeys = $args;
			//echo 'set sortkeys to $args<br>';
		} else 
		if (isset($args['sortkey'])) {
			//echo 'not so sure on this<br>';
			$sortkeys = array('key' => $args['sortkey'], 'order' => isset($args['order']) ? $args['order'] : self::DEFAULT_ORDER);
		}
		//echo "in class sortkeys =>".print_r($sortkeys,true).'<br>';


		$s = array();
		foreach($sortkeys as $k) {
			$r = new \stdClass();
			//echo 'the key thingy '.print_r($k,true).'<br>';
			if (!isset($k['key'])) {
				continue;
			}
			$r->key = $k['key'];
			//echo 'setting stdClass key to '.$k['key'].'<br>';
			if (!isset($k['order']))
			{
				$k['order'] = self::DEFAULT_ORDER;
			}
			
			$k['order'] = ($k['order'] == 'asc') || ($k['order'] == \SORT_ASC);
			//echo 'setting stdClass order to '.$k['order'].'<br>';
			$r->order = $k['order'];

			$s []= $r;
		}
		$sortkeys = $s;
		//echo "sortkeys in class after mangle => ".print_r($sortkeys,true).'<br>';
		unset($s);
		
		uasort($result['players'], function($a, $b) use($sortkeys) {
			//print_r($sortkeys);
			foreach($sortkeys as $k) {
				if (isset($a[$k->key]) && isset($b[$k->key]) && !is_array($a[$k->key]) && !is_array($b[$k->key])) {
					$ca = $a[$k->key];
					$cb = $b[$k->key];
				} else
				if (isset($a['other'][$k->key]) && isset($b['other'][$k->key]) && !is_array($a['other'][$k->key]) && !is_array($b['other'][$k->key])) {
					$ca = $a['other'][$k->key];
					$cb = $b['other'][$k->key];
				} else {
					continue;
				}

				if (is_string($ca) || is_string($cb)) {
					$res = strcasecmp("" . $ca, "" . $cb);

					if ($res == 0)
						continue;

					$res = $res < 0;
				} else {
					if ($ca === $cb)
						continue;

					$res = $ca < $cb;
				}

				if (!$k->order)
					$res = !$res;

				return ($res ? -1 : 1);
			}

			return 0;
		});
		$result['players'] = array_values($result['players']);
		return $result;
	}
	
}
