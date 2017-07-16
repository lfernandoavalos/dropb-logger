<?php

namespace Logger;

use Logger\Exceptions\LoggerException;

use DateTime;

/**
 * Count hits from a log between desired timestamp
 * 
 * @author Fernando Avalos <lfernandoavalos@gmail.com>
 * @version 1.0.0
 */
class Logger {

	/**
	 * Default fetch history since x
	 * minutes ago
	 */
	const DEFAULT_LOG_HISTORY_MINUTES = 5;

	/**
	 * Look up keys of timestamps for our logger
	 * @var array $timestamps
	 */
	private $timestamps = [];

	/**
	 * Log hits by timestamp
	 * @var array $logs
	 */
	private $logs = [];

	/**
	 * Should timestamps be deleted
	 * when not within timerange
	 * @var bool
	 */
	private $cleanup = true;

	/**
	 * Check if a given timestamp
	 * exists in log timestamp lookup array
	 * 
	 * @return bool
	 */
	public function dateTimeKeyExists(DateTime $dateTime) : bool
	{
		$timestamp = $dateTime->getTimestamp();
		return in_array($timestamp, $this->getTimestamps());
	}

	/**
	 * Set a new timestamp log lookup key
	 * 
	 * @return Loogger
	 */
	private function setLogDateTimeKey(DateTime $dateTime) : Logger
	{
		$timestamp = $dateTime->getTimestamp();
		$this->timestamps[$timestamp] = $timestamp;
		$this->logs[$timestamp] = 0;
		return $this;
	}

	/**
	 * Increment our log hit by 1
	 * @return Logger
	 */
	private function incrementLogHit(DateTime $dateTime) : Logger
	{
		$timestamp = $dateTime->getTimestamp();
		$this->logs[$timestamp]++;
		return $this;
	}

	/**
	 * Get array of timestamps in logger lookup
	 * 
	 * @param DateTime|null $dateTime
	 * @return array|int
	 */
	public function getTimestamps(DateTime $dateTime = null)
	{
		if ($dateTime) {
			$timestamp = $dateTime->getTimestamp();
			return $this->timestamps[$timestamp];
		}
		return $this->timestamps;
	}

	/**
	 * Get total size of timestamps in lookup array
	 * 
	 * @return int
	 */
	public function getTotalTimestamps() : int
	{
		return sizeof($this->getTimestamps());
	}

	/**
	 * Get current logs stored in our logger
	 * or get log by timestamp index
	 * 
	 * @param int $timestamp
	 * @return array|int
	 */
	public function getLogs(int $timestamp = null)
	{
		if ($timestamp) {
			return $this->logs[$timestamp];
		}

		return $this->logs;
	}

	/**
	 * Get size of logs
	 * @return int
	 */
	public function getLogSize() : int
	{
		return sizeof($this->getLogs());
	}

	/**
	 * Log a new hit by time or current time
	 * 
	 * @param DateTime|null $dateTime
	 * @return Logger
	 */
	public function log(DateTime $dateTime = null) : Logger
	{
		if (!$dateTime) {
			$dateTime = new \DateTime();
		}

		if (!$this->dateTimeKeyExists($dateTime)) {
			$this->setLogDateTimeKey($dateTime);
		}

		$this->incrementLogHit($dateTime);

		return $this;
	}

	/**
	 * Construct default to date from date
	 * @param DateTime $toDate
	 * @return DateTime
	 */
	private function getDefaultFromDateTime(DateTime $toDate) : DateTime
	{
		$date = new DateTime();
		$date->setTimestamp($toDate->getTimestamp());
		$date->modify('-' . self::DEFAULT_LOG_HISTORY_MINUTES . ' minutes');

		return $date;
	}

	/**
	 * Should our logger cleanup timestamp keys
	 * @return bool
	 */
	private function shouldCleanUp() : bool
	{
		return $this->cleanup;
	}

	/**
	 * Prevent our logger from deleting logs
	 * @return Logger
	 */
	public function keepLogs() : Logger
	{
		$this->cleanup = false;
		return $this;
	}

	/**
	 * Remove timestamp lookup key
	 * @param int $timestamp
	 * @return void
	 */
	private function deleteTimestampKey(int $timestamp)
	{
		unset($this->timestamps[$timestamp]);
	}

	/**
	 * Check if log date is between desired dates
	 * @param DateTime $fromDate
	 * @param DateTime $logDate
	 * @param DateTime $toDate
	 * 
	 * @return bool
	 */
	private function logDateIsBetween(DateTime $fromDate,
		DateTime $logDate,
		DateTime $toDate) : bool
	{
		return ($logDate >= $fromDate) && ($logDate <= $toDate);
	}

	/**
	 * Get hits from the last x minutes since timestamp
	 * 
	 * @param DateTime|null $toDate
	 * @param DateTime|null $fromDate
	 * 
	 * @return int
	 */
	public function getHits(DateTime $toDate = null, 
		DateTime $fromDate = null) : int
	{
		if (!$toDate) {
			$toDate = new DateTime();
		}

		if (!$fromDate) {
			$fromDate = $this->getDefaultFromDateTime($toDate);
		}
		
		if ($toDate < $fromDate) {
			throw new LoggerException("toDate must be greater than from date", 1);
		}

		$timestamps = $this->getTimestamps();
		$hits = 0;
		foreach ($timestamps as $timestamp) {
			$logDate = new DateTime();
			$logDate->setTimestamp($timestamp);

			if ($this->logDateIsBetween($fromDate, $logDate, $toDate)) {
				$hits += $this->getLogs($timestamp);
			} else if($this->shouldCleanUp()) {
				$this->deleteTimestampKey($timestamp);
			}
		}

		return $hits;
	}
}