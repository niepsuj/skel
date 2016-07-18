<?php

namespace Skel;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class ConsoleHelper
{
    protected $input;
    protected $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

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
        $helper = new QuestionHelper();
        return $helper->ask($this->input, $this->output, $question);
    }

    public function table(array $header = [])
    {
        $table = new Table($this->output);
        if(!empty($header)){
            $table->setHeaders($header);
        }

        return $table;
    }

    public function progress($count)
    {
        return new ProgressBar($this->output, $count);
    }

    public function bullets($data)
    {
        foreach($data as $title => $value){
            $this->output->writeln('  * '.$title.': <info>'.$value.'</info>');
        }
    }
}