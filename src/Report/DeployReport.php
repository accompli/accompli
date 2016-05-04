<?php

namespace Accompli\Report;

use Accompli\Console\Helper\TitleBlock;

/**
 * DeployReport.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class DeployReport extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    protected $messages = array(
        TitleBlock::STYLE_SUCCESS => 'Successfully deployed release.',
        TitleBlock::STYLE_ERRORED_SUCCESS => 'Deployed release. Some errors occured during deployment.',
        TitleBlock::STYLE_FAILURE => 'Deployment of release failed.',
    );
}
