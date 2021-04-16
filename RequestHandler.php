<?php
/**
 * 
 */
interface RequestHandler
{
	public function handleRequest($relativeUrlParts, $method, $data);
}
?>