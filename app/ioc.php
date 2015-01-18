<?php

App::bind('Helpers\JsonRPCClientInterface', 'Helpers\BitcoinCoreJsonRPCClient');
App::bind('Helpers\DataParserInterface',    'Helpers\RemoteDataParser');