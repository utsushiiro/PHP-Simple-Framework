<?php

namespace psf\core;

/**
 * 各種設定(config.ini)を読み込むcoreクラス
 *
 * {@link run} により各種設定を読み込む。この処理はシステムのbootstrap.phpにて行われる。
 *
 * まず、configsディレクトリ以下にあるconfig.ini(ベースのconfig.ini)を読み込む。
 * 続いて、ベースのconfig.iniにて設定されている実行環境(COREセクションのEXECUTION_ENVIRONMENTの値)に従って、
 * configsディレクトリ以下にある、この実行環境と同じ名前のディレクトリ以下にあるconfig.iniを読み込む。
 *
 * 読み込んだ設定値は {@link get} を用いて取得できる。
 * COREセクションのEXPAND_CONFIGS_TO_CONSTANTSを有効にしている場合は、
 * {@link expandConfigs2Constants} によって各設定値が定数に展開される。
 *
 * @package psf\core
 */
class ConfigLoader
{
    /**
     * configディレクトリへのパス
     *
     * @var string
     */
    private $CONFIG_ROOT;

    /**
     * configs.iniを読み込んだ内容
     *
     * @var
     */
    private static $config = false;

    /**
     * ConfigLoader constructor.
     */
    public function __construct()
    {
        $this->CONFIG_ROOT = dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR .'configs';
    }

    /**
     * 各種設定(config.ini)を読み込む
     *
     * このメソッドの呼出し後、{@link get} を用いたconfigの設定値へのアクセスが可能になる。
     * 各種設定の読み込みは最初の一度目の呼び出しでのみ行われる。
     */
    public function run()
    {
        if (self::$config === false):
            self::$config = $this->loadConfigs();
            $this->addCoreInfo2Config();

            if (self::$config['CORE']['EXPAND_CONFIGS_TO_CONSTANTS']):
                $this->expandConfigs2Constants(self::$config);
            endif;
        endif;
    }
    
    /**
     * ベースのconfig.iniと実行環境のconfig.iniをパースした結果を連想配列で返す
     *
     * @return array
     */
    private function loadConfigs(): array
    {
        $base_ini_filename = $this->CONFIG_ROOT . DIRECTORY_SEPARATOR . 'config.ini';
        $base_config = $this->parseConfigFile($base_ini_filename);

        if (!isset($base_config['CORE']['EXECUTION_ENVIRONMENT'])):
            throw new \RuntimeException("EXECUTION_ENVIRONMENT should be set in ${base_ini_filename}.");
        endif;

        $exe_env_ini_filename =
            $this->CONFIG_ROOT . DIRECTORY_SEPARATOR .
            $base_config['CORE']['EXECUTION_ENVIRONMENT'] . DIRECTORY_SEPARATOR . 'config.ini';
        $exe_env_config = $this->parseConfigFile($exe_env_ini_filename);

        return array_replace_recursive($base_config, $exe_env_config);
    }

    /**
     * ini形式のファイルをパースして結果を連想配列で返す
     *
     * @param string $filename
     * @return array
     */
    private function parseConfigFile(string $filename): array 
    {
        $config = parse_ini_file($filename, true, INI_SCANNER_NORMAL);

        if ($config === false):
            throw new \RuntimeException("Failed to parse ${filename}");
        endif;
        
        return $config;
    }

    /**
     * configの設定値を定数値として展開する
     *
     * 以下のようなconfig.iniを読み込んだconfigの場合
     * <pre>
     * [XXX]
     *  YYY = ZZZ
     * </pre>
     * 値ZZZ は 定数名PSF_XXX_YYY で取得できる。
     *
     * @param array $config
     */
    private function expandConfigs2Constants(array $config)
    {
        foreach ($config as $section_name => $parameters):
            foreach ($parameters as $name => $value):
                define('PSF_' . $section_name . '_' . $name, $value);
            endforeach;
        endforeach;
    }

    /**
     * 幾つかの定数値をconfigのPATHセクションに追加する
     *
     * 環境によって値が変わる設定項目のconfigへの追加処理用
     */
    private function addCoreInfo2Config()
    {
        $framework_root_dir = dirname(__FILE__, 3);
        self::$config['PATH']['FRAMEWORK_ROOT'] = $framework_root_dir;
        self::$config['PATH']['CONFIGS_ROOT'] = $this->CONFIG_ROOT;
        self::$config['PATH']['CONTROLLERS_ROOT'] =
            $framework_root_dir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'controllers';
        self::$config['PATH']['MODELS_ROOT'] =
            $framework_root_dir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'models';
        self::$config['PATH']['VIEWS_ROOT'] =
            $framework_root_dir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'views';
    }

    /**
     * configの設定値を取得する
     *
     * 以下のようなconfig.iniを読み込んだ場合
     * <pre>
     * [XXX]
     *  YYY = ZZZ
     * </pre>
     * ZZZの値は $section_name = XXX, $value_name = YYY で取得できる。
     *
     * config.iniをまだ読み込んでいない状態で呼ばれた場合、実行時例外を送出する。
     *
     * @param string $section_name セクション名
     * @param string $value_name パラメータの名前
     * @return mixed
     */
    public static function get(string $section_name, string $value_name)
    {
        if (self::$config === false):
            throw new \RuntimeException(
                'Config should be initialized by ConfigLoader::run() before using ConfigLoader::get()'
            );
        endif;

        return self::$config[$section_name][$value_name];
    }
}