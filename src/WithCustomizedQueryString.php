<?php

namespace Livewire;

trait WithCustomizedQueryString
{
    /**
     * Should return a php array when there is an array in the fromQueryString
     * If fromQueryString is not an array this should return a string
     *
     * @param string $property
     * @param string $fromQueryString
     * @return array|string
     */
    abstract public function formatQueryParameter(string $property, string $fromQueryString);

    /**
     * Format the query string
     *
     * @param $queryParams
     * @return string
     */
    abstract public function formatQueryString($queryParams): string;
}
