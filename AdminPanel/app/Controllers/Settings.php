<?php
/**
 * By MahmoudAp
 * Github: https://github.com/mahmoud-ap
 */

namespace App\Controllers;

class Settings extends BaseController
{

    public function __construct()
    {
        $this->data["activeMenu"]     = "settings";
        parent::__construct($this);
    }

    public function index($request, $response, $args)
    {
        enqueueScriptFooter(assets("vendor/jquery-validate/jquery.validate.min.js"));

        $sModel     = new \App\Models\Settings($this);
        $settings   = $sModel->getSettings();


        $viewData = [];
        $viewData["pageTitle"]      = "تنظیمات اصلی";
        $viewData["viewContent"]    = "settings/index.php";
        $viewData["activeTab"]      = "main";
        $viewData["activePage"]     = "settings";
        $viewData["settings"]       = $settings;
        $this->render($viewData);
    }

    public function backup($request, $response, $args)
    {
        enqueueScriptFooter(assets("vendor/jquery-validate/jquery.validate.min.js"));
        enqueueScriptFooter(assets("vendor/datatable/datatables.js"));
        enqueueStyleHeader(assets("vendor/datatable/datatables.css"));

        $bModel         = new \App\Models\Backup($this);
        $backupFiles    = $bModel->getUserBackups();

        $viewData = [];
        $viewData["pageTitle"]      = "پشتیبان گیری";
        $viewData["viewContent"]    = "settings/index.php";
        $viewData["activeTab"]      = "backup";
        $viewData["activePage"]     = "backup";
        $viewData["backupFiles"]    = $backupFiles;
        $this->render($viewData);
    }

    public function api($request, $response, $args)
    {
        enqueueScriptFooter(assets("vendor/jquery-validate/jquery.validate.min.js"));
        enqueueScriptFooter(assets("vendor/datatable/datatables.js"));
        enqueueStyleHeader(assets("vendor/datatable/datatables.css"));

        $viewData = [];
        $viewData["pageTitle"]      = "مدیریت API";
        $viewData["viewContent"]    = "settings/index.php";
        $viewData["activeTab"]      = "api";
        $viewData["activePage"]     = "public_api";
        $this->render($viewData);
    }


    public function filtering()
    {
        // $data = [];
        // $serverip = $_SERVER["SERVER_ADDR"];
        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, "https://check-host.net/check-tcp?host=" . $serverip . ":" . env('PORT_SSH') . "&max_nodes=50");
        // curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // $headers = ["Accept: application/json", "Cache-Control: no-cache"];
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // $response = curl_exec($ch);
        // curl_close($ch);
        // $array = json_decode($response, true);
        // $resultlink = "https://check-host.net/check-result/" . $array["request_id"];
        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, $resultlink);
        // curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // $headers = ["Accept: application/json", "Cache-Control: no-cache"];
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // sleep(3);
        // $server_output = curl_exec($ch);
        // curl_close($ch);
        // $array2 = json_decode($server_output, true);
        // foreach ($array2 as $key => $value) {
        //     $flag = str_replace(".node.check-host.net", "", $key);
        //     $flag = preg_replace("/[0-9]+/", "", $flag);
        //     if ($flag == "ir" || $flag == "us" || $flag == "fr" || $flag == "de") {
        //         if (is_numeric($value[0]["time"])) {
        //             $status = "Online";
        //         } else {
        //             $status = "Filter";
        //         }
        //         $data[] = [
        //             "flag" => $flag,
        //             "status" => $status
        //         ];
        //     }
        // }
        // $data = json_decode(json_encode($data));
    }

    public function ajaxSaveSettings($request, $response, $args)
    {
        $validator = new  \App\Validations\Settings();

        $uid        = $request->getAttribute('uid');
        $pdata      = $request->getParsedBody();
        $validate   = $validator->saveMainSettings($pdata);
        if ($validate["status"] == "error") {
            return $response->withStatus(400)->withJson($validate);
        }

        $sModel = new \App\Models\Settings($this);
        $result = $sModel->saveMainSettings($pdata, $uid);
        return $response->withStatus(200);
    }

    public function ajaxAaddPublicApi($request, $response, $args)
    {
        $validator = new  \App\Validations\Settings();

        $uid        = $request->getAttribute('uid');
        $pdata      = $request->getParsedBody();
        $validate   = $validator->addPublicApi($pdata);
        if ($validate["status"] == "error") {
            return $response->withStatus(400)->withJson($validate);
        }

        $pModel = new \App\Models\PublicApis($this);
        $result = $pModel->saveApi($pdata, $uid);
        return $response->withStatus(200)->withJson($result);
    }


    public function ajaxListPublicApi($request, $response, $args)
    {
        $pdata      = $request->getParsedBody();
        $pModel = new \App\Models\PublicApis($this);
        $result = $pModel->dataTableList($pdata);
        return $response->withStatus(200)->withJson($result);
    }


    public function ajaxDeletePublicApi($request, $response, $args)
    {
        $validator  = new  \App\Validations\Settings();

        $apiId      = $args["id"];
        $uid        = $request->getAttribute('uid');
        $validate   = $validator->publicApiInfo($apiId);
        if ($validate["status"] == "error") {
            return $response->withStatus(400)->withJson($validate);
        }

        $pModel = new \App\Models\PublicApis($this);
        $pModel->deleteApi($apiId, $uid);

        return $response->withStatus(200);
    }


    public function ajaxImportBackup($request, $response, $args)
    {
        $validator      = new  \App\Validations\Settings();
        $pdata          = $request->getParsedBody();

        $upFiles        = $request->getUploadedFiles();
        $pdata["file"]  = null;
        if (!empty($upFiles["sql_file"])) {
            $pdata["file"]  = $upFiles["sql_file"];
        }

        $validate   = $validator->importBackup($pdata);
        if ($validate["status"] == "error") {
            return $response->withStatus(400)->withJson($validate);
        }

        $sqlContent = $pdata["file"]->getStream()->getContents();
        $importFrom = $pdata["import_from"];

        $bkModel = new \App\Models\Backup();

        //import from shahan
        if ($importFrom == "shahan") {
            try {
                $values = parseSQLFileForTables($sqlContent, ["users", "Traffic"]);
                $totalInsert =  $bkModel->importBackupFromShahan($values);

                return $response->withStatus(200)->withJson(["total_insert" => $totalInsert]);
            } catch (\Exception $err) {
                return $response->withStatus(400)->withJson([
                    "messages" => "خطایی رخ داد لطفا دوباره تلاش کنید"
                ]);
            }
            //import from xpanel
        } else if ($importFrom == "xpanel") {

            try {
                $values = parseSQLFileForTables($sqlContent, ["users", "traffic"]);
                $totalInsert =  $bkModel->importBackupFromXpanel($values);
                return $response->withStatus(200)->withJson(["total_insert" => $totalInsert]);
            } catch (\Exception $err) {
                return $response->withStatus(400)->withJson([
                    "messages" => "خطایی رخ داد لطفا دوباره تلاش کنید"
                ]);
            }
        } else if ($importFrom == "current") {

            $totalInsert =  $bkModel->importSelfBackup($sqlContent);
            return $response->withStatus(200)->withJson(["total_insert" => $totalInsert]);
        }
    }

    public function ajaxCreateBackup($request, $response, $args)
    {
        $appName    = getenv("APP_NAME");
        $date       = jdate()->format("Y-m-d_H-i");
        $backupName = "$appName-$date";

        $backupPath     = PATH_ASSETS . DS . "backup";
        $backupFilePath = $backupPath . DS . "$backupName.sql";

        if (!is_dir($backupPath)) {
            @mkdir($backupPath, 0777, true);
        }

        \App\Libraries\UserShell::createMysqlBackup($backupFilePath);
    }

    public function ajaxDeleteExportFile($request, $response, $args)
    {
        $pdata      = $request->getParsedBody();
        if (!empty($pdata["filename"])) {

            $filename = $pdata["filename"];

            $filePath = PATH_ASSETS . DS . "backup" . DS . $filename;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }
}
