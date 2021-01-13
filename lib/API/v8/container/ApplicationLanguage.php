<?php

/**
 * @param \Psr\Container\ContainerInterface $container
 * @return \ICTCRM\Utility\ApplicationLanguage
 */
$container['ApplicationLanguages'] = function ($container) {
    $applicationLanguage = new \ICTCRM\Utility\ApplicationLanguage();
    return $applicationLanguage;
};
