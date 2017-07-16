# Logger 

We want to log and count hits from a log bewteen now and x minutes ago

## Requirments
- `php 7+`
- `composer`

## Setup
- Run `git clone git@github.com:lfernandoavalos/dropb-logger.git`
- Run tests `composer test`

## Usage
```
$logger = new Logger();

// Logs with current timestamp
$logger->log();

// Logs with current date time -5 minutes
$logger->log(new DateTime('now -5 minutes'));

// Logs with current date time -5 minutes - 1 seconds
// This will not appear as a hit since it is not within the default timestamp
$logger->log(new DateTime('now -5 minutes -1 seconds'));

// Will get hits from 
// 5 minutes ago up to now
// that would be 2 hits
$logger->getHits();

// You an also fetch logs from a prevoius timestamp
// this would fetch logs from (new time - 5 minutes) up to (new time)
$upToDateTime = new DateTime('now -5 minutes);
$logger->getHits($upToDateTime);