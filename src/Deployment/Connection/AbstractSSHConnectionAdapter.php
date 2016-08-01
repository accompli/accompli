<?php

namespace Accompli\Deployment\Connection;

/**
 * AbstractSSHConnectionAdapter.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
abstract class AbstractSSHConnectionAdapter implements ConnectionAdapterInterface
{
    /**
     * The hostname to connect to.
     *
     * @var string
     */
    protected $hostname;

    /**
     * The username used for authentication.
     *
     * @var string
     */
    protected $authenticationUsername;

    /**
     * Returns the username of the user executing the script.
     *
     * @return string
     */
    protected function getCurrentUsername()
    {
        return $_SERVER['USER'];
    }

    /**
     * Returns the 'home' directory for the user.
     *
     * @return string|null
     */
    protected function getUserDirectory()
    {
        $userDirectory = null;
        if (isset($_SERVER['HOME'])) {
            $userDirectory = $_SERVER['HOME'];
        } elseif (isset($_SERVER['USERPROFILE'])) {
            $userDirectory = $_SERVER['USERPROFILE'];
        }
        $userDirectory = realpath($userDirectory.'/../');
        $userDirectory .= '/'.$this->authenticationUsername;

        return $userDirectory;
    }

    /**
     * Returns the filtered output of the command.
     * Removes the command echo and shell prompt from the output.
     *
     * @param string $output
     * @param string $command
     *
     * @return string
     */
    protected function getFilteredOutput($output, $command)
    {
        $output = str_replace(array("\r\n", "\r"), array("\n", ''), $output);

        $matches = array();
        if (preg_match($this->getOutputFilterRegex($command), $output, $matches) === 1) {
            $output = ltrim($matches[1]);
        }

        return $output;
    }

    /**
     * Returns the output filter regex to filter the output.
     *
     * @param string $command
     *
     * @return string
     */
    protected function getOutputFilterRegex($command)
    {
        $commandCharacters = str_split(preg_quote($command, '/'));
        $commandCharacterRegexWhitespaceFunction = function ($value) {
            if ($value !== '\\') {
                $value .= '\s?';
            }

            return $value;
        };

        $commandCharacters = array_map($commandCharacterRegexWhitespaceFunction, $commandCharacters);

        return sprintf('/%s(.*)%s/s', implode('', $commandCharacters), substr($this->getShellPromptRegex(), 1, -1));
    }

    /**
     * Returns the regex matching the shell prompt.
     *
     * @return string
     */
    protected function getShellPromptRegex()
    {
        return sprintf('/%s@.*[$|#]/', $this->authenticationUsername);
    }
}
