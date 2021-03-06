<?php

namespace InstagramTakipci\IO;

use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class BufferIO extends ConsoleIO
{
    /**
     * @param string                   $input
     * @param int                      $verbosity
     * @param OutputFormatterInterface $formatter
     */
    public function __construct($input = '', $verbosity = null, OutputFormatterInterface $formatter = null)
    {
        $input = new StringInput($input);
        $input->setInteractive(false);

        $output = new StreamOutput(fopen('php://memory', 'rw'), $verbosity === null ? StreamOutput::VERBOSITY_NORMAL : $verbosity, !empty($formatter), $formatter);

        parent::__construct($input, $output, new HelperSet(array()));
    }

    public function getOutput()
    {
        fseek($this->output->getStream(), 0);

        $output = stream_get_contents($this->output->getStream());

        $output = preg_replace_callback("{(?<=^|\n|\x08)(.+?)(\x08+)}", function ($matches) {
            $pre = strip_tags($matches[1]);

            if (strlen($pre) === strlen($matches[2])) {
                return '';
            }

            // TODO reverse parse the string, skipping span tags and \033\[([0-9;]+)m(.*?)\033\[0m style blobs
            return rtrim($matches[1])."\n";
        }, $output);

        return $output;
    }
}
