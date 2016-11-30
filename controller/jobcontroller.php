<?php
/**
 * ownCloud - renderedcom
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author IvonneBurgos <irburgos@espol.edu.ec>
 * @copyright IvonneBurgos 2016
 */

namespace OCA\RenderEdcom\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OC\Files\Filesystem as Filesystem;
use OC\Files\Storage\Local as Local;


class JobController extends Controller{
    
	private $userId;
  
	public function __construct($AppName, IRequest $request, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
	}

	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function createJob($scene,$file_path,$frame_ini,$frame_fin){
        $date = time(); 
        $directory = explode('/', $file_path, -1);
        $directory = join('/', $directory);
        $frame_inicio = (int) preg_replace('/[^0-9]/', '', $frame_ini);
        $frame_final = (int) preg_replace('/[^0-9]/', '', $frame_fin);

       /* if (file_exists('/var/tmp/afanasy/jobs/0/'. $scene)) {
            $result = 'ok';
            $confirmation =  false;
        } else {*/
            $varpath = $this->createFolder($scene,$date);
            $data = array('user'=> $this->userId,'scene'=> $scene."_".$date, 'directory'=>$directory, 'file_path'=>$file_path,'frame_ini'=> $frame_inicio,'frame_fin'=> $frame_final, 'pathSave'=> $varpath);
            $result = shell_exec('sh /opt/cgru/setup3.sh; python "/opt/cgru/afanasy/python/job6.py" ' . escapeshellarg(json_encode($data)));
            $confirmation = true;
        //}
            
        return new DataResponse(['result' => $result, 'confirmation' => $confirmation]);
    }
    
    protected function createFolder($scene,$date){
        
        $array = ["datadir" => "Nube_Multimedia"];  
        $datadir = new Local($array);
        $varfolder= $this->userId . '/' . $scene . "_". $date.'/';
        $datadir->mkdir($varfolder); 
        $result= shell_exec('chmod 777 -R /var/www/owncloud/Nube_Multimedia/'. $this->userId .'/' . $scene . "_" . $date); 
        return $varfolder;
    }

   public function findFolder($scene){
        $ban= false;

        $id = $scene;
        $handler = opendir(__DIR__ . '\var\tmp\afanasy\0');
        while ($file = readdir($handler))
        {
         if ($file !== "." && $file !== "..")
        {
         preg_match("/({$scene})/i" , $file, $name);
        //echo isset($name[0]) ? $name[0] . "\n\n" : '';
        }
        }
        if (count($name) > 0){
         $ban = true;
        }
         closedir($handler);

        return new DataResponse ($ban);

    }

}