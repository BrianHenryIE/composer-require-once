<?php

namespace BrianHenryIE\ComposerRequireOnce;

use Composer\Autoload\ClassMapGenerator;
use PhpParser\ParserFactory;

class Scan {

	public function generateForDirectory( string $dir ): array {

		$dirMap = ClassMapGenerator::createMap($dir);

		$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

		/**
		 * @var array<string, array{className: string, requirements:array<string>} $classRequires An array keyed by className which contains a key `requirements` containing all other classnames that must be loaded first.
		 */
		$classRequires = array();

		foreach( $dirMap as $className => $filePath ) {

			$classRequires[ $className ] = array(
				'className' => $className,
				'requirements' => array(),
			);

			$code = file_get_contents( $filePath );

			$clasAsts = $parser->parse( $code );

			$requires = array();

			foreach( $clasAsts as $ast ) {
				if( ! empty( $ast->extends ) ) {
					foreach ( $ast->extends as $extends ) {
						foreach( $extends as $requirement ) {
							if ( isset( $dirMap[ $requirement ] ) ) {
								$requires[] = $requirement;
							}
						}
					}
				}
				if( ! empty($ast->implements ) ) {
					foreach ( $ast->implements as $implements ) {
						foreach( $implements->parts as $requirement ) {
							if ( isset( $dirMap[ $requirement ] ) ) {
								$requires[] = $requirement;
							}
						}
					}
				}
			}

			$classRequires[ $className ]['requirements'] = $requires;
		}


		$sortedClassRequires = array();

		while( count( $classRequires ) > 0 ) {

			foreach ( $classRequires as $className => $element ) {

				if( empty( $element['requirements'] ) ) {
					array_unshift( $sortedClassRequires, $className );
					unset( $classRequires[$className] );
				}

				$allAlreadyContainedInSortedArray = array_reduce( $element['requirements'], function( bool $carry, $className ) use ( $sortedClassRequires ) {
					return $carry && in_array( $className, $sortedClassRequires );
				}, true);

				if( $allAlreadyContainedInSortedArray ) {
					$sortedClassRequires[] = $className;
					unset( $classRequires[$className] );
				}

			}

		}


		$sortedDirMap = array();
		foreach($sortedClassRequires as $classname ) {
			$sortedDirMap[$classname] = $dirMap[$classname];
		}

		return $sortedDirMap;

	}

}