<?php

namespace Skel\Console;

use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class Helper
{
    protected $input;
    protected $output;

    public function ask($text, $default = null)
    {
        return $this->question(new Question($text, $default));
    }

    public function confirm($text)
    {
        return $this->question(new ConfirmationQuestion($text, false, '/^(y|yes)$/i'));
    }

    public function choice($text, $answers, $default = 1)
    {
        return $this->question(new ChoiceQuestion($text, $answers, $default));
    }

    public function question(Question $question)
    {
        if(null !== $this->input && null !== $this->output){
            $helper = new QuestionHelper();
            return $helper->ask($this->input, $this->output, $question);
        }
    }

    public function formatVariable($value, $length = 50)
    {
        switch(true){
            case is_bool($value):
                if($value){
                    return '<fg=green>TRUE</fg=green>';
                }

                return '<fg=red>FALSE</fg=red>';

            case is_numeric($value):
                return (string) $value;

            case is_string($value):
                $format = new FormatterHelper();
                return $format->truncate($value, $length);

            case is_array($value):
                return $this->formatVariable(implode(',', $value), $length);

            case is_object($value):
                return $this->formatVariable(json_encode($value), $length);

            case is_null($value):
                return '<fg=red>-</fg=red>';
        }

        return '-';
    }

    public function formatListVariable($value, $styles = null)
    {
        if(null === $styles){
            return $value;
        }

        if(!isset($styles[$value])){
            return $value;
        }

        return '<fg='.$styles[$value].'>'.$this->formatVariable($value).'</fg='.$styles[$value].'>';
    }

    public function table(array $header = [], $data = null, $json = false)
    {
        if(null !== $this->input && null !== $this->output) {
            if ($json) {
                $this->output->writeln(json_encode($data));
                return;
            }

            $table = new Table($this->output);
            $filters = [];
            if (!empty($header)) {
                $table->setHeaders(
                    array_map(function ($header, $key) use (&$filters) {
                        $filters[$key] = [$this, 'formatVariable'];

                        if (is_array($header)) {
                            if (isset($header['filter'])) {
                                if (is_callable($header['filter'])) {
                                    $filters[$key] = $header['filter'];
                                } elseif (is_array($header['filter'])) {
                                    $callback = [$this, 'formatListVariable'];
                                    $styles = $header['filter'];
                                    $filters[$key] = function ($value) use ($callback, $styles) {
                                        return call_user_func($callback, $value, $styles);
                                    };
                                }
                            }

                            if (isset($header['map'])) {
                                if (is_callable($header['map'])) {
                                    $mapCallback = $header['map'];
                                    if (isset($filters[$key]) && is_callable($filters[$key])) {
                                        $filterCallback = $filters[$key];
                                        $filters[$key] = function ($item) use ($mapCallback, $filterCallback) {
                                            return call_user_func($filterCallback,
                                                call_user_func($mapCallback, $item)
                                            );
                                        };
                                    } else {
                                        $filters[$key] = $mapCallback;
                                    }
                                }
                            }

                            if (isset($header['label'])) {
                                return $header['label'];
                            }
                        } elseif (is_string($header)) {
                            return $header;
                        }

                        return ucfirst($key);
                    }, array_values($header), array_keys($header))
                );
            }

            if (null === $data) {
                return $table;
            }

            foreach ($data as $item) {
                if (is_array($item)) {
                    $row = [];
                    $i = 0;
                    foreach ($filters as $key => $value) {
                        switch (true) {
                            case array_key_exists($key, $item):
                                $row[] = $value($item[$key]);
                                break;
                            case array_key_exists($i, $item):
                                $row[] = $value($item[$i]);
                                break;
                            default:
                                $row[] = $value($item);
                                break;
                        }

                        $i++;
                    }

                    $table->addRow($row);
                } else {
                    $table->addRow([$item]);
                }
            }

            $table->render();
        }
    }

    public function progress($count)
    {
        if(null !== $this->input && null !== $this->output) {
            return new ProgressBar($this->output, $count);
        }
    }

    public function bullets($data, $json = false, $bullet = '  * ')
    {
        if(null !== $this->input && null !== $this->output) {
            if ($json) {
                $this->output->writeln(json_encode($data));
                return;
            }

            foreach ($data as $title => $value) {
                if (is_array($value)) {
                    $this->output->writeln($bullet . $title . ':');
                    $this->bullets($value, false, '  ' . $bullet);
                } else {
                    $this->output->writeln($bullet . $title . ': <info>' . $this->formatVariable($value, 1000) . '</info>');
                }
            }
        }
    }

    public function text($template, $data, $json = false)
    {
        if(null !== $this->input && null !== $this->output) {
            if ($json) {
                $this->output->writeln(json_encode($data));
                return;
            }

            $this->output->writeln(
                preg_replace_callback('/:(\w+)/', function ($matches) use (&$data) {
                    if (isset($data[$matches[1]])) {
                        return $this->formatVariable($data[$matches[1]]);
                    }
                    return $matches[0];
                }, $template)
            );
        }
    }

    public function error($message, $json = false)
    {
        if(null !== $this->input && null !== $this->output) {
            if ($json) {
                $this->output->writeln(json_encode(['error' => true, 'message' => $message]));
            }

            $format = new FormatterHelper();
            $this->output->writeln(
                $format->formatBlock($message, 'error')
            );
        }
    }

    /**
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @param mixed $input
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param mixed $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }
}