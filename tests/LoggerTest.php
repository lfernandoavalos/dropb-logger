<?php

namespace Logger;

use PHPUnit\Framework\TestCase;

use DateTime;

class HelloTest extends TestCase
{
	/**
	 * Test logger wrote to timestamps
	 */
    public function testLoggerAddTimestamp()
    {
    	$logger = new Logger();
    	$logger->log();

    	$this->assertEquals(1, $logger->getTotalTimestamps());
    }

    /**
     * Test logger added a log by given datetime
     */
    public function testAddLogByDate()
    {
    	$someDate = new DateTime('now -5 minutes');
    	$now = new DateTime('now');
    	$logger = new Logger();
    	$logger->log($someDate);

    	$timestamp = $logger->getTimestamps($someDate);

    	$this->assertNotEmpty($timestamp);
    	$this->assertEquals($someDate->getTimestamp(), $timestamp);
    	$this->assertNotEquals($someDate->format('Y-m-d H:i'), 
    		$now->format('Y-m-d H:i'));
    }

    /**
     * Test logger writes to logs
     */
    public function testLoggerAddedHit()
    {
    	$logger = new Logger();
    	$logger->log();

    	$this->assertEquals(1, $logger->getLogSize());
    }

    /**
     * Test empty logger
     */
    public function testGetEmptyHits()
    {
    	$logger = new Logger();

    	$this->assertEquals(0, $logger->getHits());
    }

    public function testLoggerIncrementSameTimestamp()
    {
        $logger = new Logger();
        $now = new DateTime('2017-01-01 00:00:00');

        $logger->log($now);
        $logger->log($now);
        $logger->log($now);

        $this->assertEquals(3, $logger->getHits($now));
        $this->assertEquals(3, $logger->getLogs($now->getTimestamp()));
    }

    /**
     * Test our logger returns correct count
     * of hits
     */
    public function testLoggerHasHitsNow()
    {
    	$logger = new Logger();
    	$logger->log();
    	$logger->log();
    	$logger->log();

    	$this->assertEquals(3, $logger->getHits());
    }

    /**
     * Test logger counts hits between date ranges
     * 
     * Include all b/w
     * 2017-01-01 00:05:01 & 2017-01-01 00:00:01
     * 
     * Ignore +x minutes and -x minutes not within
     * the given 5 minute range
     */
    public function testLoggerGetsHitsBetweenDateTimes()
    {
    	$logger = new Logger();
    	/***********************************
    	* Add log for 2017-01-01 00:05:01
    	***********************************/
    	$now = new DateTime('2017-01-01 00:05:01');
    	$logger->log($now);
    	
    	/***********************************
    	* Add log for 2017-01-01 00:00:01
    	***********************************/
    	$now5MinAgo = new DateTime();
    	$now5MinAgo->setTimestamp($now->getTimestamp());
    	$now5MinAgo->modify('-5 minutes');
    	$logger->log($now5MinAgo);


    	/***********************************
    	* Add log for 2017-01-01 00:06:01
    	***********************************/
    	$now6MinIntoTheFuture = new DateTime();
    	$now6MinIntoTheFuture->setTimestamp($now->getTimestamp());
    	$now6MinIntoTheFuture->modify('+6 minutes');
    	$logger->log($now6MinIntoTheFuture);

    	/***********************************
    	* Add log for 2017-01-01 00:05:00
    	***********************************/
    	$now1SecAgo = new DateTime();
    	$now1SecAgo->setTimestamp($now->getTimestamp());
    	$now1SecAgo->modify('-1 seconds');
    	$logger->log($now1SecAgo);

        /***********************************
        * Add log for 2017-01-01 00:05:00
        * This timestamp should not exist in our logs
        ***********************************/
        $now5Min1SecondAgo = new DateTime();
        $now5Min1SecondAgo->setTimestamp($now->getTimestamp());
        $now5Min1SecondAgo->modify('-5 minutes -1 seconds');
        $logger->log($now5Min1SecondAgo);

    	$this->assertEquals(3, $logger->getHits($now));
    	$this->assertFalse($logger->dateTimeKeyExists($now5Min1SecondAgo));
    }

    public function testKeepLogs()
    {
        $logger = new Logger();
        $logger->keepLogs();
        /***********************************
        * Add log for 2017-01-01 00:05:01
        ***********************************/
        $now = new DateTime('2017-01-01 00:05:01');
        $logger->log($now);

        /***********************************
        * Add log for 2017-01-01 00:05:00
        * This timestamp should not exist in our logs
        ***********************************/
        $now5Min1SecondAgo = new DateTime();
        $now5Min1SecondAgo->setTimestamp(
            $now->getTimestamp());
        $now5Min1SecondAgo->modify('-5 minutes -1 seconds');
        $logger->log($now5Min1SecondAgo);

        $this->assertEquals(1, $logger->getHits($now));
        $this->assertEquals(1, $logger->getHits($now5Min1SecondAgo));
    }

    /**
     * Test when getting hits between dates that
     * from date is less than to date
     */
    public function testLoggerGetsHitsBetweenDateTimesException()
    {
    	$toDate = new DateTime();
    	$logger = new Logger();
    	$logger->log();

    	$this->expectException('\Logger\Exceptions\LoggerException');
    	$hits = $logger->getHits($toDate, new DateTime('now + 30 minutes'));
    }
}