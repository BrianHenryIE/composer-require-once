<?php

namespace BrianHenryIE\ComposerRequireOnce;

use Error;
use PHPUnit\Framework\TestCase;

class ScanTest extends TestCase {


	public function testGenerateForDir(): void {

		$dir = __DIR__ . '/../rmccue/requests/library';

		$sut = new Scan();

		$result = $sut->generateForDirectory( $dir );

		foreach( $result as $classname => $path ) {
			try {
				require_once $path;
			} catch( Error $error ) {
				$this->fail( $error->getMessage() );
			}

		}

		$this->assertTrue(true);
	}

}