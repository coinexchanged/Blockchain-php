<?php

/**
 * Laravel IDE Helper Generator
 *
 * @author    Barry vd. Heuvel <barryvdh@gmail.com>
 * @copyright 2014 Barry vd. Heuvel / Fruitcake Studio (http://www.fruitcakestudio.nl)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/barryvdh/laravel-ide-helper
 */

namespace Barryvdh\LaravelIdeHelper\Console;

use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Context;
use Barryvdh\Reflection\DocBlock\Serializer as DocBlockSerializer;
use Barryvdh\Reflection\DocBlock\Tag;
use Composer\Autoload\ClassMapGenerator;
use Illuminate\Console\Command;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\ContextFactory;
use ReflectionClass;
use ReflectionObject;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command to generate autocomplete information for your IDE
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */
class ModelsCommand extends Command
{
    /**
     * @var Filesystem $files
     */
    protected $files;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'ide-helper:models';
    protected $filename = '_ide_helper_models.php';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate autocompletion for models';

    protected $write_model_magic_where;
    protected $write_model_relation_count_properties;
    protected $properties = array();
    protected $methods = array();
    protected $write = false;
    protected $dirs = array();
    protected $reset;
    protected $keep_text;
    protected $phpstorm_noinspections;
    /**
     * @var bool[string]
     */
    protected $nullableColumns = [];

    /**
     * During initializtion we use Laravels Date Facade to
     * determine the actual date class and store it here.
     *
     * @var string
     */
    protected $dateClass;

    /**
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $filename = $this->option('filename');
        $this->write = $this->option('write');
        $this->dirs = array_merge(
            $this->laravel['config']->get('ide-helper.model_locations'),
            $this->option('dir')
        );
        $model = $this->argument('model');
        $ignore = $this->option('ignore');
        $this->reset = $this->option('reset');
        $this->phpstorm_noinspections = $this->option('phpstorm-noinspections');
        if ($this->option('smart-reset')) {
            $this->keep_text = $this->reset = true;
        }
        $this->write_model_magic_where = $this->laravel['config']->get('ide-helper.write_model_magic_where', true);
        $this->write_model_relation_count_properties =
            $this->laravel['config']->get('ide-helper.write_model_relation_count_properties', true);

        //If filename is default and Write is not specified, ask what to do
        if (!$this->write && $filename === $this->filename && !$this->option('nowrite')) {
            if (
                $this->confirm(
                    "Do you want to overwrite the existing model files? Choose no to write to $filename instead"
                )
            ) {
                $this->write = true;
            }
        }

        $this->dateClass = class_exists(\Illuminate\Support\Facades\Date::class)
            ? '\\' . get_class(\Illuminate\Support\Facades\Date::now())
            : '\Illuminate\Support\Carbon';

        $content = $this->generateDocs($model, $ignore);

        if (!$this->write) {
            $written = $this->files->put($filename, $content);
            if ($written !== false) {
                $this->info("Model information was written to $filename");
            } else {
                $this->error("Failed to write model information to $filename");
            }
        }
    }


    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
          array('model', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Which models to include', array()),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
          array('filename', 'F', InputOption::VALUE_OPTIONAL, 'The path to the helper file', $this->filename),
          array('dir', 'D', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
              'The model dir, supports glob patterns', array()),
          array('write', 'W', InputOption::VALUE_NONE, 'Write to Model file'),
          array('nowrite', 'N', InputOption::VALUE_NONE, 'Don\'t write to Model file'),
          array('reset', 'R', InputOption::VALUE_NONE, 'Remove the original phpdocs instead of appending'),
          array('smart-reset', 'r', InputOption::VALUE_NONE, 'Refresh the properties/methods list, but keep the text'),
          array('phpstorm-noinspections', 'p', InputOption::VALUE_NONE,
              'Add PhpFullyQualifiedNameUsageInspection and PhpUnnecessaryFullyQualifiedNameInspection PHPStorm ' .
              'noinspection tags'
          ),
          array('ignore', 'I', InputOption::VALUE_OPTIONAL, 'Which models to ignore', ''),
        );
    }

    protected function generateDocs($loadModels, $ignore = '')
    {


        $output = "<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */
\n\n";

        $hasDoctrine = interface_exists('Doctrine\DBAL\Driver');

        if (empty($loadModels)) {
            $models = $this->loadModels();
        } else {
            $models = array();
            foreach ($loadModels as $model) {
                $models = array_merge($models, explode(',', $model));
            }
        }

        $ignore = array_merge(
            explode(',', $ignore),
            $this->laravel['config']->get('ide-helper.ignored_models', array())
        );

        foreach ($models as $name) {
            if (in_array($name, $ignore)) {
                if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                    $this->comment("Ignoring model '$name'");
                }
                continue;
            }
            $this->properties = array();
            $this->methods = array();
            if (class_exists($name)) {
                try {
                    // handle abstract classes, interfaces, ...
                    $reflectionClass = new ReflectionClass($name);

                    if (!$reflectionClass->isSubclassOf('Illuminate\Database\Eloquent\Model')) {
                        continue;
                    }

                    if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                        $this->comment("Loading model '$name'");
                    }

                    if (!$reflectionClass->IsInstantiable()) {
                        // ignore abstract class or interface
                        continue;
                    }

                    $model = $this->laravel->make($name);

                    if ($hasDoctrine) {
                        $this->getPropertiesFromTable($model);
                    }

                    if (method_exists($model, 'getCasts')) {
                        $this->castPropertiesType($model);
                    }

                    $this->getPropertiesFromMethods($model);
                    $this->getSoftDeleteMethods($model);
                    $this->getCollectionMethods($model);
                    $output                .= $this->createPhpDocs($name);
                    $ignore[]              = $name;
                    $this->nullableColumns = [];
                } catch (\Throwable $e) {
                    $this->error("Exception: " . $e->getMessage() .
                        "\nCould not analyze class $name.\n\nTrace:\n" .
                        $e->getTraceAsString());
                }
            }
        }

        if (!$hasDoctrine) {
            $this->error(
                'Warning: `"doctrine/dbal": "~2.3"` is required to load database information. ' .
                'Please require that in your composer.json and run `composer update`.'
            );
        }

        return $output;
    }


    protected function loadModels()
    {
        $models = array();
        foreach ($this->dirs as $dir) {
            $dir = base_path() . '/' . $dir;
            $dirs = glob($dir, GLOB_ONLYDIR);
            foreach ($dirs as $dir) {
                if (file_exists($dir)) {
                    $classMap = ClassMapGenerator::createMap($dir);

                    // Sort list so it's stable across different environments
                    ksort($classMap);

                    foreach ($classMap as $model => $path) {
                        $models[] = $model;
                    }
                }
            }
        }
        return $models;
    }

    /**
     * cast the properties's type from $casts.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    protected function castPropertiesType($model)
    {
        $casts = $model->getCasts();
        foreach ($casts as $name => $type) {
            switch ($type) {
                case 'boolean':
                case 'bool':
                    $realType = 'boolean';
                    break;
                case 'string':
                    $realType = 'string';
                    break;
                case 'array':
                case 'json':
                    $realType = 'array';
                    break;
                case 'object':
                    $realType = 'object';
                    break;
                case 'int':
                case 'integer':
                case 'timestamp':
                    $realType = 'integer';
                    break;
                case 'real':
                case 'double':
                case 'float':
                    $realType = 'float';
                    break;
                case 'date':
                case 'datetime':
                    $realType = $this->dateClass;
                    break;
                case 'collection':
                    $realType = '\Illuminate\Support\Collection';
                    break;
                default:
                    $realType = class_exists($type) ? ('\\' . $type) : 'mixed';
                    break;
            }

            if (!isset($this->properties[$name])) {
                continue;
            } else {
                $realType = $this->checkForCustomLaravelCasts($realType);
                $realType = $this->getTypeOverride($realType);
                $this->properties[$name]['type'] = $this->getTypeInModel($model, $realType);

                if (isset($this->nullableColumns[$name])) {
                    $this->properties[$name]['type'] .= '|null';
                }
            }
        }
    }

    /**
     * Returns the overide type for the give type.
     *
     * @param string $type
     * @return string|null
     */
    protected function getTypeOverride($type)
    {
        $typeOverrides = $this->laravel['config']->get('ide-helper.type_overrides', array());

        return isset($typeOverrides[$type]) ? $typeOverrides[$type] : $type;
    }

    /**
     * Load the properties from the database table.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    protected function getPropertiesFromTable($model)
    {
        $table = $model->getConnection()->getTablePrefix() . $model->getTable();
        $schema = $model->getConnection()->getDoctrineSchemaManager($table);
        $databasePlatform = $schema->getDatabasePlatform();
        $databasePlatform->registerDoctrineTypeMapping('enum', 'string');

        $platformName = $databasePlatform->getName();
        $customTypes = $this->laravel['config']->get("ide-helper.custom_db_types.{$platformName}", array());
        foreach ($customTypes as $yourTypeName => $doctrineTypeName) {
            $databasePlatform->registerDoctrineTypeMapping($yourTypeName, $doctrineTypeName);
        }

        $database = null;
        if (strpos($table, '.')) {
            list($database, $table) = explode('.', $table);
        }

        $columns = $schema->listTableColumns($table, $database);

        if ($columns) {
            foreach ($columns as $column) {
                $name = $column->getName();
                if (in_array($name, $model->getDates())) {
                    $type = $this->dateClass;
                } else {
                    $type = $column->getType()->getName();
                    switch ($type) {
                        case 'string':
                        case 'text':
                        case 'date':
                        case 'time':
                        case 'guid':
                        case 'datetimetz':
                        case 'datetime':
                        case 'decimal':
                            $type = 'string';
                            break;
                        case 'integer':
                        case 'bigint':
                        case 'smallint':
                            $type = 'integer';
                            break;
                        case 'boolean':
                            switch (config('database.default')) {
                                case 'sqlite':
                                case 'mysql':
                                    $type = 'integer';
                                    break;
                                default:
                                    $type = 'boolean';
                                    break;
                            }
                            break;
                        case 'float':
                            $type = 'float';
                            break;
                        default:
                            $type = 'mixed';
                            break;
                    }
                }

                $comment = $column->getComment();
                if (!$column->getNotnull()) {
                    $this->nullableColumns[$name] = true;
                }
                $this->setProperty(
                    $name,
                    $this->getTypeInModel($model, $type),
                    true,
                    true,
                    $comment,
                    !$column->getNotnull()
                );
                if ($this->write_model_magic_where) {
                    $this->setMethod(
                        Str::camel("where_" . $name),
                        $this->getClassNameInDestinationFile($model, \Illuminate\Database\Eloquent\Builder::class)
                        . '|'
                        . $this->getClassNameInDestinationFile($model, get_class($model)),
                        array('$value')
                    );
                }
            }
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    protected function getPropertiesFromMethods($model)
    {
        $methods = get_class_methods($model);
        if ($methods) {
            sort($methods);
            foreach ($methods as $method) {
                if (
                    Str::startsWith($method, 'get') && Str::endsWith(
                        $method,
                        'Attribute'
                    ) && $method !== 'getAttribute'
                ) {
                    //Magic get<name>Attribute
                    $name = Str::snake(substr($method, 3, -9));
                    if (!empty($name)) {
                        $reflection = new \ReflectionMethod($model, $method);
                        $type = $this->getReturnType($reflection);
                        $type = $this->getTypeInModel($model, $type);
                        $this->setProperty($name, $type, true, null);
                    }
                } elseif (
                    Str::startsWith($method, 'set') && Str::endsWith(
                        $method,
                        'Attribute'
                    ) && $method !== 'setAttribute'
                ) {
                    //Magic set<name>Attribute
                    $name = Str::snake(substr($method, 3, -9));
                    if (!empty($name)) {
                        $this->setProperty($name, null, null, true);
                    }
                } elseif (Str::startsWith($method, 'scope') && $method !== 'scopeQuery') {
                    //Magic set<name>Attribute
                    $name = Str::camel(substr($method, 5));
                    if (!empty($name)) {
                        $reflection = new \ReflectionMethod($model, $method);
                        $args = $this->getParameters($reflection);
                        //Remove the first ($query) argument
                        array_shift($args);
                        $builder = $this->getClassNameInDestinationFile(
                            $reflection->getDeclaringClass(),
                            \Illuminate\Database\Eloquent\Builder::class
                        );
                        $modelName = $this->getClassNameInDestinationFile(
                            $reflection->getDeclaringClass(),
                            $reflection->getDeclaringClass()->getName()
                        );
                        $this->setMethod($name, $builder . '|' . $modelName, $args);
                    }
                } elseif (in_array($method, ['query', 'newQuery', 'newModelQuery'])) {
                    $builder = $this->getClassNameInDestinationFile($model, get_class($model->newModelQuery()));

                    $this->setMethod(
                        $method,
                        $builder . "|" . $this->getClassNameInDestinationFile($model, get_class($model))
                    );
                } elseif (
                    !method_exists('Illuminate\Database\Eloquent\Model', $method)
                    && !Str::startsWith($method, 'get')
                ) {
                    //Use reflection to inspect the code, based on Illuminate/Support/SerializableClosure.php
                    $reflection = new \ReflectionMethod($model, $method);

                    if ($returnType = $reflection->getReturnType()) {
                        $type = $returnType instanceof \ReflectionNamedType
                            ? $returnType->getName()
                            : (string)$returnType;
                    } else {
                        // php 7.x type or fallback to docblock
                        $type = (string)$this->getReturnTypeFromDocBlock($reflection);
                    }

                    $file = new \SplFileObject($reflection->getFileName());
                    $file->seek($reflection->getStartLine() - 1);

                    $code = '';
                    while ($file->key() < $reflection->getEndLine()) {
                        $code .= $file->current();
                        $file->next();
                    }
                    $code = trim(preg_replace('/\s\s+/', '', $code));
                    $begin = strpos($code, 'function(');
                    $code = substr($code, $begin, strrpos($code, '}') - $begin + 1);

                    foreach (
                        array(
                               'hasMany' => '\Illuminate\Database\Eloquent\Relations\HasMany',
                               'hasManyThrough' => '\Illuminate\Database\Eloquent\Relations\HasManyThrough',
                               'hasOneThrough' => '\Illuminate\Database\Eloquent\Relations\HasOneThrough',
                               'belongsToMany' => '\Illuminate\Database\Eloquent\Relations\BelongsToMany',
                               'hasOne' => '\Illuminate\Database\Eloquent\Relations\HasOne',
                               'belongsTo' => '\Illuminate\Database\Eloquent\Relations\BelongsTo',
                               'morphOne' => '\Illuminate\Database\Eloquent\Relations\MorphOne',
                               'morphTo' => '\Illuminate\Database\Eloquent\Relations\MorphTo',
                               'morphMany' => '\Illuminate\Database\Eloquent\Relations\MorphMany',
                               'morphToMany' => '\Illuminate\Database\Eloquent\Relations\MorphToMany',
                               'morphedByMany' => '\Illuminate\Database\Eloquent\Relations\MorphToMany'
                             ) as $relation => $impl
                    ) {
                        $search = '$this->' . $relation . '(';
                        if (stripos($code, $search) || ltrim($impl, '\\') === ltrim((string)$type, '\\')) {
                            //Resolve the relation's model to a Relation object.
                            $methodReflection = new \ReflectionMethod($model, $method);
                            if ($methodReflection->getNumberOfParameters()) {
                                continue;
                            }

                            // Adding constraints requires reading model properties which
                            // can cause errors. Since we don't need constraints we can
                            // disable them when we fetch the relation to avoid errors.
                            $relationObj = Relation::noConstraints(function () use ($model, $method) {
                                return $model->$method();
                            });

                            if ($relationObj instanceof Relation) {
                                $relatedModel = $this->getClassNameInDestinationFile(
                                    $model,
                                    get_class($relationObj->getRelated())
                                );

                                $relations = [
                                    'hasManyThrough',
                                    'belongsToMany',
                                    'hasMany',
                                    'morphMany',
                                    'morphToMany',
                                    'morphedByMany',
                                ];
                                if (strpos(get_class($relationObj), 'Many') !== false) {
                                    //Collection or array of models (because Collection is Arrayable)
                                    $relatedClass = '\\' . get_class($relationObj->getRelated());
                                    $collectionClass = $this->getCollectionClass($relatedClass);
                                    $collectionClassNameInModel = $this->getClassNameInDestinationFile(
                                        $model,
                                        $collectionClass
                                    );
                                    $this->setProperty(
                                        $method,
                                        $collectionClassNameInModel . '|' . $relatedModel . '[]',
                                        true,
                                        null
                                    );
                                    if ($this->write_model_relation_count_properties) {
                                        $this->setProperty(
                                            Str::snake($method) . '_count',
                                            'int|null',
                                            true,
                                            false
                                        );
                                    }
                                } elseif ($relation === "morphTo") {
                                    // Model isn't specified because relation is polymorphic
                                    $this->setProperty(
                                        $method,
                                        $this->getClassNameInDestinationFile($model, Model::class) . '|\Eloquent',
                                        true,
                                        null
                                    );
                                } else {
                                    //Single model is returned
                                    $this->setProperty(
                                        $method,
                                        $relatedModel,
                                        true,
                                        null,
                                        '',
                                        $this->isRelationNullable($relation, $relationObj)
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Check if the relation is nullable
     *
     * @param string   $relation
     * @param Relation $relationObj
     *
     * @return bool
     */
    protected function isRelationNullable(string $relation, Relation $relationObj): bool
    {
        $reflectionObj = new ReflectionObject($relationObj);

        if (in_array($relation, ['hasOne', 'hasOneThrough', 'morphOne'], true)) {
            $defaultProp = $reflectionObj->getProperty('withDefault');
            $defaultProp->setAccessible(true);

            return !$defaultProp->getValue($relationObj);
        }

        if (!$reflectionObj->hasProperty('foreignKey')) {
            return false;
        }

        $fkProp = $reflectionObj->getProperty('foreignKey');
        $fkProp->setAccessible(true);

        return isset($this->nullableColumns[$fkProp->getValue($relationObj)]);
    }

    /**
     * @param string      $name
     * @param string|null $type
     * @param bool|null   $read
     * @param bool|null   $write
     * @param string|null $comment
     * @param bool        $nullable
     */
    protected function setProperty($name, $type = null, $read = null, $write = null, $comment = '', $nullable = false)
    {
        if (!isset($this->properties[$name])) {
            $this->properties[$name] = array();
            $this->properties[$name]['type'] = 'mixed';
            $this->properties[$name]['read'] = false;
            $this->properties[$name]['write'] = false;
            $this->properties[$name]['comment'] = (string) $comment;
        }
        if ($type !== null) {
            $newType = $this->getTypeOverride($type);
            if ($nullable) {
                $newType .= '|null';
            }
            $this->properties[$name]['type'] = $newType;
        }
        if ($read !== null) {
            $this->properties[$name]['read'] = $read;
        }
        if ($write !== null) {
            $this->properties[$name]['write'] = $write;
        }
    }

    protected function setMethod($name, $type = '', $arguments = array())
    {
        $methods = array_change_key_case($this->methods, CASE_LOWER);

        if (!isset($methods[strtolower($name)])) {
            $this->methods[$name] = array();
            $this->methods[$name]['type'] = $type;
            $this->methods[$name]['arguments'] = $arguments;
        }
    }

    /**
     * @param string $class
     * @return string
     */
    protected function createPhpDocs($class)
    {

        $reflection = new ReflectionClass($class);
        $namespace = $reflection->getNamespaceName();
        $classname = $reflection->getShortName();
        $originalDoc = $reflection->getDocComment();
        $keyword = $this->getClassKeyword($reflection);
        $interfaceNames = array_diff_key(
            $reflection->getInterfaceNames(),
            $reflection->getParentClass()->getInterfaceNames()
        );

        if ($this->reset) {
            $phpdoc = new DocBlock('', new Context($namespace));
            if ($this->keep_text) {
                $phpdoc->setText(
                    (new DocBlock($reflection, new Context($namespace)))->getText()
                );
            }
        } else {
            $phpdoc = new DocBlock($reflection, new Context($namespace));
        }

        if (!$phpdoc->getText()) {
            $phpdoc->setText($class);
        }

        $properties = array();
        $methods = array();
        foreach ($phpdoc->getTags() as $tag) {
            $name = $tag->getName();
            if ($name == "property" || $name == "property-read" || $name == "property-write") {
                $properties[] = $tag->getVariableName();
            } elseif ($name == "method") {
                $methods[] = $tag->getMethodName();
            }
        }

        foreach ($this->properties as $name => $property) {
            $name = "\$$name";

            if ($this->hasCamelCaseModelProperties()) {
                $name = Str::camel($name);
            }

            if (in_array($name, $properties)) {
                continue;
            }
            if ($property['read'] && $property['write']) {
                $attr = 'property';
            } elseif ($property['write']) {
                $attr = 'property-write';
            } else {
                $attr = 'property-read';
            }

            $tagLine = trim("@{$attr} {$property['type']} {$name} {$property['comment']}");
            $tag = Tag::createInstance($tagLine, $phpdoc);
            $phpdoc->appendTag($tag);
        }

        ksort($this->methods);

        foreach ($this->methods as $name => $method) {
            if (in_array($name, $methods)) {
                continue;
            }
            $arguments = implode(', ', $method['arguments']);
            $tag = Tag::createInstance("@method static {$method['type']} {$name}({$arguments})", $phpdoc);
            $phpdoc->appendTag($tag);
        }

        if ($this->write && ! $phpdoc->getTagsByName('mixin')) {
            $eloquentClassNameInModel = $this->getClassNameInDestinationFile($reflection, 'Eloquent');
            $phpdoc->appendTag(Tag::createInstance("@mixin " . $eloquentClassNameInModel, $phpdoc));
        }
        if ($this->phpstorm_noinspections) {
            /**
             * Facades, Eloquent API
             * @see https://www.jetbrains.com/help/phpstorm/php-fully-qualified-name-usage.html
             */
            $phpdoc->appendTag(Tag::createInstance("@noinspection PhpFullyQualifiedNameUsageInspection", $phpdoc));
            /**
             * Relations, other models in the same namespace
             * @see https://www.jetbrains.com/help/phpstorm/php-unnecessary-fully-qualified-name.html
             */
            $phpdoc->appendTag(
                Tag::createInstance("@noinspection PhpUnnecessaryFullyQualifiedNameInspection", $phpdoc)
            );
        }

        $serializer = new DocBlockSerializer();
        $serializer->getDocComment($phpdoc);
        $docComment = $serializer->getDocComment($phpdoc);


        if ($this->write) {
            $filename = $reflection->getFileName();
            $contents = $this->files->get($filename);
            if ($originalDoc) {
                $contents = str_replace($originalDoc, $docComment, $contents);
            } else {
                $replace = "{$docComment}\n";
                $pos = strpos($contents, "final class {$classname}") ?: strpos($contents, "class {$classname}");
                if ($pos !== false) {
                    $contents = substr_replace($contents, $replace, $pos, 0);
                }
            }
            if ($this->files->put($filename, $contents)) {
                $this->info('Written new phpDocBlock to ' . $filename);
            }
        }

        $output = "namespace {$namespace}{\n{$docComment}\n\t{$keyword}class {$classname} extends \Eloquent ";

        if ($interfaceNames) {
            $interfaces = implode(', \\', $interfaceNames);
            $output .= "implements \\{$interfaces} ";
        }

        return $output . "{}\n}\n\n";
    }

    /**
     * Get the parameters and format them correctly
     *
     * @param $method
     * @return array
     */
    public function getParameters($method)
    {
        //Loop through the default values for paremeters, and make the correct output string
        $params = array();
        $paramsWithDefault = array();
        /** @var \ReflectionParameter $param */
        foreach ($method->getParameters() as $param) {
            $paramClass = $param->getClass();
            $paramStr = (!is_null($paramClass) ? '\\' . $paramClass->getName() . ' ' : '') . '$' . $param->getName();
            $params[] = $paramStr;
            if ($param->isOptional() && $param->isDefaultValueAvailable()) {
                $default = $param->getDefaultValue();
                if (is_bool($default)) {
                    $default = $default ? 'true' : 'false';
                } elseif (is_array($default)) {
                    $default = '[]';
                } elseif (is_null($default)) {
                    $default = 'null';
                } elseif (is_int($default)) {
                    //$default = $default;
                } else {
                    $default = "'" . trim($default) . "'";
                }
                $paramStr .= " = $default";
            }
            $paramsWithDefault[] = $paramStr;
        }
        return $paramsWithDefault;
    }

    /**
     * Determine a model classes' collection type.
     *
     * @see http://laravel.com/docs/eloquent-collections#custom-collections
     * @param string $className
     * @return string
     */
    protected function getCollectionClass($className)
    {
        // Return something in the very very unlikely scenario the model doesn't
        // have a newCollection() method.
        if (!method_exists($className, 'newCollection')) {
            return '\Illuminate\Database\Eloquent\Collection';
        }

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $className();
        return '\\' . get_class($model->newCollection());
    }

    /**
     * @return bool
     */
    protected function hasCamelCaseModelProperties()
    {
        return $this->laravel['config']->get('ide-helper.model_camel_case_properties', false);
    }

    protected function getReturnType(\ReflectionMethod $reflection): ?string
    {
        $type = $this->getReturnTypeFromDocBlock($reflection);
        if ($type) {
            return $type;
        }

        return $this->getReturnTypeFromReflection($reflection);
    }

    /**
     * Get method return type based on it DocBlock comment
     *
     * @param \ReflectionMethod $reflection
     *
     * @return null|string
     */
    protected function getReturnTypeFromDocBlock(\ReflectionMethod $reflection)
    {
        $phpDocContext = (new ContextFactory())->createFromReflector($reflection);
        $context = new Context(
            $phpDocContext->getNamespace(),
            $phpDocContext->getNamespaceAliases()
        );
        $type = null;
        $phpdoc = new DocBlock($reflection, $context);

        if ($phpdoc->hasTag('return')) {
            $type = $phpdoc->getTagsByName('return')[0]->getType();
        }

        return $type;
    }

    protected function getReturnTypeFromReflection(\ReflectionMethod $reflection): ?string
    {
        $returnType = $reflection->getReturnType();
        if (!$returnType) {
            return null;
        }

        $type = $returnType instanceof \ReflectionNamedType
            ? $returnType->getName()
            : (string)$returnType;

        if (!$returnType->isBuiltin()) {
            $type = '\\' . $type;
        }

        if ($returnType->allowsNull()) {
            $type .= '|null';
        }

        return $type;
    }


    /**
     * Generates methods provided by the SoftDeletes trait
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    protected function getSoftDeleteMethods($model)
    {
        $traits = class_uses(get_class($model), true);
        if (in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', $traits)) {
            $modelName = $this->getClassNameInDestinationFile($model, get_class($model));
            $builder = $this->getClassNameInDestinationFile($model, \Illuminate\Database\Query\Builder::class);
            $this->setMethod('withTrashed', $builder . '|' . $modelName, []);
            $this->setMethod('withoutTrashed', $builder . '|' . $modelName, []);
            $this->setMethod('onlyTrashed', $builder . '|' . $modelName, []);
        }
    }

    /**
     * Generates methods that return collections
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    protected function getCollectionMethods($model)
    {
        $collectionClass = $this->getCollectionClass(get_class($model));

        if ($collectionClass !== '\\' . \Illuminate\Database\Eloquent\Collection::class) {
            $collectionClassInModel = $this->getClassNameInDestinationFile($model, $collectionClass);

            $this->setMethod('get', $collectionClassInModel . '|static[]', ['$columns = [\'*\']']);
            $this->setMethod('all', $collectionClassInModel . '|static[]', ['$columns = [\'*\']']);
        }
    }

    /**
     * @param ReflectionClass $reflection
     * @return string
     */
    protected function getClassKeyword(ReflectionClass $reflection)
    {
        if ($reflection->isFinal()) {
            $keyword = 'final ';
        } elseif ($reflection->isAbstract()) {
            $keyword = 'abstract ';
        } else {
            $keyword = '';
        }

        return $keyword;
    }

    /**
     * @param  string  $type
     * @return string|null
     * @throws \ReflectionException
     */
    protected function checkForCustomLaravelCasts(string $type): ?string
    {
        if (!class_exists($type) || !interface_exists(CastsAttributes::class)) {
            return $type;
        }

        $reflection = new \ReflectionClass($type);

        if (!$reflection->implementsInterface(CastsAttributes::class)) {
            return $type;
        }

        $methodReflection = new \ReflectionMethod($type, 'get');

        $type = $this->getReturnTypeFromReflection($methodReflection);

        if ($type === null) {
            $type = $this->getReturnTypeFromDocBlock($methodReflection);
        }

        return $type;
    }

    /**
     * @param object|ReflectionClass  $model
     * @param string $type
     * @return string
     */
    protected function getTypeInModel(object $model, ?string $type): ?string
    {
        if ($type === null) {
            return null;
        }

        if (class_exists($type)) {
            $type = $this->getClassNameInDestinationFile($model, $type);
        }

        return $type;
    }

    /**
     * @param object|ReflectionClass $model
     * @param string $className
     * @return string
     */
    protected function getClassNameInDestinationFile(object $model, string $className): string
    {
        $reflection = $model instanceof ReflectionClass
            ? $model
            : new ReflectionObject($model)
        ;

        $className = trim($className, '\\');
        $writingToExternalFile = !$this->write;
        $classIsNotInExternalFile = $reflection->getName() !== $className;

        if ($writingToExternalFile && $classIsNotInExternalFile) {
            return '\\' . $className;
        }

        $usedClassNames = $this->getUsedClassNames($reflection);
        return $usedClassNames[$className] ?? ('\\' . $className);
    }

    /**
     * @param ReflectionClass $reflection
     * @return string[]
     */
    protected function getUsedClassNames(ReflectionClass $reflection): array
    {
        $namespaceAliases = array_flip((new ContextFactory())->createFromReflector($reflection)->getNamespaceAliases());
        $namespaceAliases[$reflection->getName()] = $reflection->getShortName();

        return $namespaceAliases;
    }
}
