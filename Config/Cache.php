<?php
declare(strict_types=1);

/*
 * @author Martin Fris <rasta@lj.sk>
 */

namespace BetterSerializerBundle\Config;

/**
 *
 */
interface Cache
{

    /**
     * @const string
     */
    public const APCU = 'apcu';

    /**
     * @const string
     */
    public const FILESYSTEM = 'filesystem';
}
