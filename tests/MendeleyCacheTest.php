<?php
class MendeleyCacheTest extends UnitTestCase {
	function testCacheSetAndGet() {
		$name = 'test';
		$value = 123;
		$consumer = Configuration::getConsumer();
		$suffix = '_' . md5($consumer['key']);
		$cache = new MendeleyCache($suffix);
		$dir = $cache->getDir();

		$cache->del($name);
		$this->assertFalse(file_exists($dir . $name . $suffix));

		$cache->set($name, $value);
		$this->assertTrue(file_exists($dir . $name . $suffix));

		$test = $cache->get($name);
		$this->assertTrue($test === $value);

		$cache->del($name);
		$this->assertFalse(file_exists($dir . $name . $suffix));
	}
}
