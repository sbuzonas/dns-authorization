<?php
namespace FancyGuy\DnsAuthorization\Result;

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
class SRVResult extends AbstractResult {

	/**
	 * @var integer
	 */
	protected $priority;

	/**
	 * @var integer
	 */
	protected $weight;

	/**
	 * @var integer
	 */
	protected $port;

	/**
	 * @return integer
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * @return integer
	 */
	public function getWeight() {
		return $this->weight;
	}

	/**
	 * @return integer
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * @param integer $priority
	 * @return \FancyGuy\DnsAuthorization\Record\SRVRecord
	 */
	public function setPriority($priority) {
		$this->priority = (int) $priority;
		return $this;
	}

	/**
	 * @param integer $weight
	 * @return \FancyGuy\DnsAuthorization\Record\SRVRecord
	 */
	public function setWeight($weight) {
		$this->weight = $weight;
		return $this;
	}

	/**
	 * @param integer $port
	 * @return \FancyGuy\DnsAuthorization\Record\SRVRecord
	 */
	public function setPort($port) {
		$this->port = $port;
		return $this;
	}

	/**
	 * @param string $result
	 * @return static
	 */
	public static function createFromSRVResultString($result) {
		$record = new static();
		list($priority, $weight, $port, $target) = array_slice(explode(' ', $result), 1);
		$record->setPort($port)->setPriority($priority)->setTarget($target)->setWeight($weight);
		return $record;
	}

	public function __toString() {
		return sprintf('SRV %s %s %s %s', $this->priority, $this->weight, $this->port, $this->target);
	}

}
