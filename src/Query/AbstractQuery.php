<?php
namespace FancyGuy\DnsAuthorization\Query;

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

/**
 * @author Steve Buzonas <steve@fancyguy.com>
 */
abstract class AbstractQuery implements QueryInterface {

	private $hostname;
	private $rawQueryResult;

	public function __construct($hostname) {
		$this->hostname = $hostname;
	}

	public function getHostname() {
		return $this->hostname;
	}

	public function getRawQueryResult() {
		if (!$this->rawQueryResult) {
			$this->performQuery();
		}
		return $this->rawQueryResult;
	}

	public function getQueryResult() {
		if (!$this->isQueryResolved()) {
			$results = array();
			foreach ($this->getRawQueryResult() as $result) {
				$results[] = sprintf("%s %s", $this->getQueryTypeString(), $result[$this->getQueryResultField()]);
			}
			return $results;
		} else {
			return array(sprintf("%s %s", $this->getQueryTypeString(), $this->hostname));
		}
	}

	protected function isQueryResolved() {
		return false;
	}

	protected function performQuery() {
		$this->rawQueryResult = dns_get_record($this->hostname, $this->getQueryType());
	}

	final public function getQuery() {
		return sprintf("%s %s", $this->getQueryTypeString(), $this->getHostname());
	}

	public function getQueryTypeString() {
		switch ($this->getQueryType()) {
			case QueryInterface::A_QUERY:
				return 'A';
			case QueryInterface::AAAA_QUERY:
				return 'AAAA';
			case QueryInterface::CNAME_QUERY:
				return 'CNAME';
			case QueryInterface::NS_QUERY:
				return 'NS';
			case QueryInterface::SRV_QUERY:
				return 'SRV';
			case QueryInterface::TXT_QUERY:
				return 'TXT';
			default:
				throw new \InvalidArgumentException("Attempting to get type string for undefined query type: %s", $this->getQueryType());
		}
	}

}
