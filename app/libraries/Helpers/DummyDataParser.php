<?php
/**
 * Created by PhpStorm.
 * User: A
 * Date: 18/01/2015
 * Time: 15:00
 */

namespace Helpers;


class DummyDataParser implements DataParserInterface {

	public function fetchUrl( $url ) {
		return '*ok*';
	}
}