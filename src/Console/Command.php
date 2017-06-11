<?php

namespace Skel\Console;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Komenda symfony uzupełniona o kod pozwalający na definiowanie komend w naturalny dla silexa sposób:
 *
 * $command = new ConsoleCommand('<comand name> -no-value-option|alias {argument}');
 * $command->setCode(function(<args>){});
 */
class Command extends BaseCommand
{

    /**
     * Wyrażenia regularne parsujące definicję komendy
     */
    const DEF_BASE = '/^(?<command>[\w\-\.]+){1}(?<input>.*)?$/';
    const DEF_ARGS = '/\s+\{(?<arguments>\w+)(?<array>\[\])?\}/';
    const DEF_OPTS = '/\s+(?<value>\-{1,2})(?<options>[\w\-]+(\|\w{1})?)(?<array>\[\])?/';

    /**
     * Lista zdefiniowanych argumentów i opcji które nie są definiowane od razu w klasie bazowej przez co możliwa jest ich edycja
     * 
     * @var array
     */
    private $definitions = array();
    
    /**
     * Akcja jaka ma być uruchomiona, może być to domknięcie, albo obiekt implementujący metodę __invoke albo tablica [obiekt,klasa|nazwa metody]
     * 
     * @var mixed
     */
    private $_code = null;

    /**
     * Modyfikacja konstruktora względem oryginału obejmuje w głównej mierze parsowanie parametrów z ciągu znaków przekazanego
     *
     * Format:
     *   <name -option1 -option2|o --option3 --option4[] {argument1} {argument2[]}>
     * Gdzie:
     *   - name - nazwa akcji
     *   - option1 - dodanie jednego myślnika (-) definiuje opcję bez wartości (flagę)
     *   - option2 - dodanie po nazwie opcji pipe'a (|) definiuje jednoliterowy alias
     *   - option3 - dodanie dwóch myślników (--) definiuje opcję która może posiadać wartość
     *   - option4 - dodanie nawiasów kwadratowych ([]) definiuje że opcja może być użyta wielokrotnie a wartości przekazywane są jako tablica
     *   - argument1 - argument wywołania
     *   - argument2 - ostatni argument jeśli ma nawiasy kwadratowe ([]) zbierać będzie wszystkie pozostałe argumenty (nienazwane w definicji) i przekazywać jako tablica
     * 
     * @see  http://symfony.com/doc/current/cookbook/console/console_command.html
     * @param string $definition
     * @throws \Exception
     */
    public function __construct($definition)
    {
        $matches = [];

        // Wyłuskanie nazwy akcji
        $base = preg_match(self::DEF_BASE, $definition, $matches);
        if (!isset($matches['command']))
            throw new \Exception('Nieprawidłowa definicja komendy: <command :argument -options|o>');

        // Konfiguracja klasy Symfony
        parent::__construct($matches['command']);

        // Parsowanie pozostałej części definicji
        if (!empty($matches['input'])) {
            $input = $matches['input'];

            /**
             * Parsowanie argumentów
             */
            preg_match_all(self::DEF_ARGS, $input, $matches);
            if (!empty($matches['arguments'])) {
                foreach ($matches['arguments'] as $i => $name) {

                    if ($matches['array'][$i] == '[]') {
                        $mode = InputArgument::REQUIRED | InputArgument::IS_ARRAY;
                    } else {
                        $mode = InputArgument::REQUIRED;
                    }

                    $this->definitions[$name] = array(
                        'mode' => $mode,
                        'description' => null,
                        'default' => null
                    );
                }
            }

            /**
             * Parsowanie opcji
             */
            preg_match_all(self::DEF_OPTS, $input, $matches);
            if (!empty($matches['options'])) {

                foreach ($matches['options'] as $i => $name) {
                    @list($name, $shortcut) = explode('|', $name);

                    if ($matches['value'][$i] == '-') {
                        $mode = InputOption::VALUE_NONE;

                        if (strlen($name) == 1 && null === $shortcut) {
                            $shortcut = $name;
                            $name = $name . '-flag';
                        }

                    } else {
                        if ($matches['array'][$i] == '[]') {
                            $mode = InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY;
                        } else {
                            $mode = InputOption::VALUE_REQUIRED;
                        }
                    }

                    $this->definitions[$name] = array(
                        'mode' => $mode,
                        'shortcut' => $shortcut,
                        'description' => null,
                        'default' => null
                    );
                }
            }
        }
    }

    /**
     * Ustawia domyślną wartość dla opcji lub argumentu (analogicznie do routingu symfony)
     * 
     * @param string $name 
     * @param string $value
     * @return self
     * @throws \Exception
     */
    public function value($name, $value)
    {
        if(PHP_SAPI === 'cli'){
            if(!isset($this->definitions[$name]))
                throw new \Exception('Invalud argument or option name: '.$name);

            $this->definitions[$name]['default'] = $value;
            $this->definitions[$name]['mode'] = $this->updateModeOptional($name);
        }

        return $this;
    }

    /**
     * Dodaje opis argumentu lub opcji, przydatne do help'a
     * 
     * @param string $name
     * @param string $value
     * @return self
     */
    public function info($name, $value)
    {
        if(PHP_SAPI === 'cli') {
            if (isset($this->definitions[$name])) {
                $this->definitions[$name]['description'] = (string)$value;
            }
        }

        return $this;
    }

    /**
     * Alias dla metody {Symfony\Component\Console\Command\Command::setHelp} w komendzie
     * 
     * @param string $value
     * @return self
     */
    public function help($value)
    {
        if(PHP_SAPI === 'cli'){
            $this->setHelp($value);
        }

        return $this;
    }

    /**
     * Alias dla metody {Symfony\Component\Console\Command\Command::setDescription} w komendzie
     * 
     * @param string $value
     * @return self
     */
    public function description($value)
    {
        if(PHP_SAPI === 'cli'){
            $this->setDescription($value);
        }

        return $this;
    }

    /**
     * Dodaje prefix dla komendy np: module:action, jest to wykożystane przy zbieraniu wszystkich
     * akcji konsolowych z kolekcji kontrollerów silexowych
     * 
     * @param string $prefix
     * @return self
     */
    public function addPrefix($prefix)
    {
        if(PHP_SAPI === 'cli'){
            if(!empty($prefix))
                $this->setName(trim($prefix,'/').':'.$this->getName());
        }

        return $this;
    }

    /**
     * Metoda zamyka proces definiowania komend. Od tego momentu zmiana konfiguracji argumentow komendy nie da efektu 
     * 
     * @return self
     */
    public function boot()
    {
        if(PHP_SAPI === 'cli'){
            foreach($this->definitions as $name => $definition){
                if(array_key_exists('shortcut', $definition)){
                    $this->addOption($name, $definition['shortcut'], $definition['mode'], $definition['description'], $definition['default']);
                }else{
                    $this->addArgument($name, $definition['mode'], $definition['description'], $definition['default']);
                }
            }
        }

        return $this;
    }

    /**
     * Nadpisuje metode i przekazuje kod do innego miejsca przez co metoda {Symfony\Component\Console\Command\Command::run} uruchomi metodę
     * {self::execute} zamiast odwoływać się do parametru {Symfony\Component\Console\Command\Command::$code}
     * 
     * @param mixed $code Callable
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setCode(callable $code)
    {
        if(PHP_SAPI === 'cli'){
            $this->_code = $code;
        }

        return $this;
    }

    /**
     * Metoda uruchamia akcję dopasowując opcję i argumenty z definicji do argumentów funkcji akcji analogicznie jak ma to miejsce w przypadku
     * uruchamiania akcji HTTP
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpKernel/Controller/ControllerResolver.php
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if(is_array($this->_code)){
            $ref = new \ReflectionMethod($this->_code[0], $this->_code[1]);
        }elseif($this->_code instanceof \Closure){
            $ref = new \ReflectionFunction($this->_code);
        }else{
            $ref = new \ReflectionObject($this->_code);
            $ref = $ref->getMethod('__invoke');
        }

        $args = array();

        foreach($ref->getParameters() as $parameter){
            if(null === $parameter->getClass()){
                $name = strtolower(preg_replace('/([a-z])([A-Z])/', "\\1-\\2", $parameter->getName()));
                if($input->hasArgument($name)) $args[] = $input->getArgument($name);
                elseif($input->hasOption($name)) $args[] = $input->getOption($name);
            }else{
                $class = $parameter->getClass();
                if($class->isInstance($input)) $args[] = $input;
                elseif($class->isInstance($output)) $args[] = $output;
            }
        }

        return call_user_func_array($this->_code, $args);
    }

    private function updateModeOptional($name){
        $type = isset($this->definitions[$name]['shortcut']);
        $optional = $type ? InputOption::VALUE_OPTIONAL : InputArgument::OPTIONAL;
        $array = $type ? InputOption::VALUE_IS_ARRAY : InputArgument::IS_ARRAY;
        return $optional | ( $this->definitions[$name]['mode'] & $array ? $array : 0);
    }
}