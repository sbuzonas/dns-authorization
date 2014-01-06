<?php
namespace FancyGuy\DnsAuthorization;

/*
 * Copyright (c) 2014, FancyGuy Technologies
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

use FancyGuy\DnsAuthorization\Repository\SRVFilterRepository;
use FancyGuy\DnsAuthorization\Result\SRVResult;
use FancyGuy\DnsAuthorization\Query\IPv4Query;
use FancyGuy\DnsAuthorization\Query\IPv6Query;
use FancyGuy\DnsAuthorization\Query\SRVQuery;
use FancyGuy\DnsAuthorization\Query\TXTQuery;

/**
 * @author Steve Buzonas <steve@fancyguy.com>
 */
class DnsAuthorization {

	const AUTH_RECORD = "FGAUTH-1";

	private $srvRepository;

	public function __construct() {
		$this->srvRepository = new SRVFilterRepository();
	}

	public function getAuthorizedHosts($hostname) {
		$authRecords = $this->getAuthRecordsForHost($hostname);
		$resolvedRecords = array();
		foreach ($authRecords as $record) {
			$resolvedRecords = array_merge($resolvedRecords, $this->handleAuthRecord($record));
		}
		return $resolvedRecords;
	}

	private function handleAuthRecord($record) {
		if (0 === stripos($record, 'AAAA')) {
			return $this->maptTrimPrefix($this->getIPv6Hosts(implode(' ', array_slice(explode(' ', $record), 1))), 'AAAA');
		} else if (0 === stripos($record, 'A')) {
			return $this->maptTrimPrefix($this->getIPv4Hosts(implode(' ', array_slice(explode(' ', $record), 1))), 'A');
		} else if (0 === stripos($record, 'SRV')) {
			return $this->maptTrimPrefix($this->getSRVHosts(implode(' ', array_slice(explode(' ', $record), 1))), 'SRV');
		}
		return array();
	}

	private function getIPv4Hosts($hostname) {
		$query = new IPv4Query($hostname);
		return $query->getQueryResult();
	}

	private function getIPv6Hosts($hostname) {
		$query = new IPv6Query($hostname);
		return $query->getQueryResult();
	}

	private function maptTrimPrefix(Array $results, $prefix) {
		foreach ($results as &$result) {
			$result = trim(substr($result, strlen($prefix)));
		}
		return $results;
	}

	private function getSRVHosts($result) {
		list($host, $params) = array_map('trim', explode('~', $result, 2));
		if ('all' !== $params) {
			list($priority, $weight, $port) = explode(' ', $params, 3);
			$this->srvRepository->setPortFilter($port)->setPriorityFilter($priority)->setWeightFilter($weight);
		} else {
			$this->srvRepository->setPortFilter(null)->setPortFilter(null)->setWeightFilter(null);
		}
		return array_map(function($srvResult) {
			return sprintf('SRV %s', implode('', array_slice(explode(' ', $srvResult), -1)));
		}, $this->srvRepository->getAllHostsFromQuery(new SRVQuery($host)));
	}

	private function getAuthRecordsForHost($hostname) {
		$txtQuery = new TXTQuery($hostname);
		$validRecords = array();
		foreach ($txtQuery->getQueryResult() as $result) {
			if (0 !== strpos($result, sprintf("TXT v=%s", self::AUTH_RECORD))) {
				continue;
			}
			// the magic +6 comes from 'TXT v=' in the string
			$validRecords[] = trim(substr($result, strlen(self::AUTH_RECORD) + 6), "; \t\n\r\0\x0b");
		}
		return $validRecords;
	}

}
