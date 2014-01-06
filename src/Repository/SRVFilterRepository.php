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

use FancyGuy\DnsAuthorization\Query\SRVQuery;
use FancyGuy\DnsAuthorization\Result\SRVResult;

/**
 * @author Steve Buzonas <steve@fancyguy.com>
 */
class SRVFilterRepository extends SRVRepository {

	/**
	 * @var string
	 */
	private $priorityFilter;

	/**
	 * @var string
	 */
	private $weightFilter;

	/**
	 * @var string
	 */
	private $portFilter;

	/**
	 * @return string
	 */
	public function getPriorityFilter() {
		return $this->priorityFilter;
	}

	/**
	 * @return string
	 */
	public function getWeightFilter() {
		return $this->weightFilter;
	}

	/**
	 * @return string
	 */
	public function getPortFilter() {
		return $this->portFilter;
	}

	/**
	 * @param string $priorityFilter
	 * @return \FancyGuy\DnsAuthorization\SRVFilterRepository
	 */
	public function setPriorityFilter($priorityFilter) {
		$this->priorityFilter = (string) $priorityFilter;
		return $this;
	}

	/**
	 * @param string $weightFilter
	 * @return \FancyGuy\DnsAuthorization\SRVFilterRepository
	 */
	public function setWeightFilter($weightFilter) {
		$this->weightFilter = (string) $weightFilter;
		return $this;
	}

	/**
	 * @param string $portFilter
	 * @return \FancyGuy\DnsAuthorization\SRVFilterRepository
	 */
	public function setPortFilter($portFilter) {
		$this->portFilter = (string) $portFilter;
		return $this;
	}

	public function getIPv4HostsFromQuery(SRVQuery $query) {
		$records = array();
		$results = parent::getIPv4HostsFromQuery($query);
		foreach ($results as $result) {
			if ($this->filterResult($result)) {
				$records[] = $result;
			}
		}
		return $records;
	}

	public function getIPv6HostsFromQuery(SRVQuery $query) {
		$records = array();
		$results = parent::getIPv6HostsFromQuery($query);
		foreach ($results as $result) {
			if ($this->filterResult($result)) {
				$records[] = $result;
			}
		}
		return $records;
	}

	private function filterResult($result) {
		$record = SRVResult::createFromSRVResultString($result);
		switch (true) {
			case ($this->filterValue($this->portFilter, $record->getPort())):
			case ($this->filterValue($this->priorityFilter, $record->getPriority())):
			case ($this->filterValue($this->weightFilter, $record->getWeight())):
				return false;
		}
		return $result;
	}

	/**
	 * @param string $filter
	 * @param string $value
	 * @return mixed
	 */
	private function filterValue($filter, $value) {
		switch (true) {
			case (ctype_digit($filter) && (int) $filter !== $value):
			case (0 === strpos($filter, '>=') && !((int) substr($filter, 2) >= $value)):
			case (0 === strpos($filter, '>') && !((int) substr($filter, 1) > $value)):
			case (0 === strpos($filter, '<=') && !((int) substr($filter, 2) <= $value)):
			case (0 === strpos($filter, '<') && !((int) substr($filter, 1) < $value)):
				return false;
		}
		return $value;
	}

}
