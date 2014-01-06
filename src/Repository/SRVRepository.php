<?php
namespace FancyGuy\DnsAuthorization\Repository;

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

use FancyGuy\DnsAuthorization\Result\SRVResult;
use FancyGuy\DnsAuthorization\Query\IPv4Query;
use FancyGuy\DnsAuthorization\Query\IPv6Query;
use FancyGuy\DnsAuthorization\Query\SRVQuery;
use FancyGuy\DnsAuthorization\Query\QueryInterface;

/**
 * @author Steve Buzonas <steve@fancyguy.com>
 */
class SRVRepository {

	/**
	 * @var Record\SRVRecord[]
	 */
	private $recordRepository = array();

	public function getAllHostsFromQuery(SRVQuery $query) {
		return array_merge($this->getIPv4HostsFromQuery($query), $this->getIPv6HostsFromQuery($query));
	}

	/**
	 * @param \FancyGuy\DnsAuthorization\Query\SRVQuery $query
	 * @return array
	 */
	public function getIPv4HostsFromQuery(SRVQuery $query) {
		$records = array();
		foreach ($query->getQueryResult() as $result) {
			$record = $this->srvRepositoryFetch($result);
			if ($record->isIPv4Target()) {
				$records[] = $result;
			} else if (!$record->isIPTarget()) {
				$records = array_merge($records, $this->convertIPtoSRV($record, new IPv4Query($record->getTarget())));
			}
		}
		return $records;
	}

	/**
	 * @param \FancyGuy\DnsAuthorization\Query\SRVQuery $query
	 * @return array
	 */
	public function getIPv6HostsFromQuery(SRVQuery $query) {
		$records = array();
		foreach ($query->getQueryResult() as $result) {
			$record = $this->srvRepositoryFetch($result);
			if ($record->isIPv6Target()) {
				$records[] = $result;
			} else if (!$record->isIPTarget()) {
				$records = array_merge($records, $this->convertIPtoSRV($record, new IPv6Query($record->getTarget())));
			}
		}
		return $records;
	}

	/**
	 * @param \FancyGuy\DnsAuthorization\Record\SRVRecord $record
	 * @param \FancyGuy\DnsAuthorization\Query\QueryInterface $query
	 * @return array
	 */
	private function convertIPtoSRV(SRVResult $record, QueryInterface $query) {
		$records = array();
		foreach ($query->getQueryResult() as $result) {
			$newResult = clone $record;
			$newResult->setTarget($this->stripPrefix($result, $query->getQueryTypeString()));
			$records[] = (string) $newResult;
		}
		return $records;
	}

	/**
	 * @param string $result
	 * @param string $queryType
	 * @return string
	 */
	private function stripPrefix($result, $queryType) {
		return trim(substr($result, strlen($queryType)));
	}

	/**
	 * @param string $resultString
	 * @return Record\SRVRecord
	 */
	protected function srvRepositoryFetch($resultString) {
		if (!array_key_exists($resultString, $this->recordRepository)) {
			$this->recordRepository[$resultString] = SRVResult::createFromSRVResultString($resultString);
		}
		return $this->recordRepository[$resultString];
	}

}
