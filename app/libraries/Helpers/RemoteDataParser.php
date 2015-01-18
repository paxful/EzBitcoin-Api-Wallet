<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 18/01/2015
 * Time: 14:59
 */

namespace Helpers;


class RemoteDataParser implements DataParserInterface {

	public function fetchUrl( $url ) {
		return file_get_contents($url);
	}
}