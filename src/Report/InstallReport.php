<?php

namespace Accompli\Report;

use Accompli\Console\Helper\TitleBlock;

/**
 * InstallReport.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class InstallReport extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    protected $messages = array(
        TitleBlock::STYLE_SUCCESS => 'Successfully installed release.',
        TitleBlock::STYLE_ERRORED_SUCCESS => 'Installed release. Some errors occured during installation.',
        TitleBlock::STYLE_FAILURE => 'Installation of release failed.',
    );
}
