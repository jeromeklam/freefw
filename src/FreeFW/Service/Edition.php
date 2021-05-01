<?php
namespace FreeFW\Service;

/**
 * Edition service
 *
 * @author jeromeklam
 */
class Edition extends \FreeFW\Core\Service
{

    public function printEdition($p_edi_id, $p_lang_id, $p_model)
    {
        $filename       = '';
        $editionVersion = null;
        $edition        = \FreeFW\Model\Edition::findFirst(['edi_id' => $p_edi_id]);
        if ($edition instanceof \FreeFW\Model\Edition) {
            foreach ($edition->getVersions() as $oneVersion) {
                if ($oneVersion->getLangId() == $p_lang_id) {
                    $editionVersion = $oneVersion;
                    break;
                }
            }
            if ($editionVersion === null) {
                foreach ($edition->getVersions() as $oneVersion) {
                    $editionVersion = $oneVersion;
                    if ($oneVersion->getLangId() == $edition->getLangId()) {
                        break;
                    }
                }
            }
        }
        if ($editionVersion) {
            if (method_exists($p_model, 'afterRead')) {
                $p_model->afterRead();
            }
            $mergeDatas = $p_model->getMergeData();
            // Get group and user
            $sso        = \FreeFW\DI\DI::getShared('sso');
            $user       = $sso->getUser();
            // @todo : rechercher le groupe principal de l'utilisateur
            $group      = \FreeSSO\Model\Group::findFirst(
                [
                    'grp_id' => 4
                ]
            );
            //
            $file = uniqid(true, 'edition');
            $src  = '/tmp/print_' . $file . '_tpl.odt';
            $dest = '/tmp/print_' . $file . '_dest.odt';
            $dPdf = '/tmp/print_' . $file . '_dest.pdf';
            $ediContent = $edition->getEdiContent();
            file_put_contents($src, $ediContent);
            file_put_contents($dest, $ediContent);
            $mergeDatas->addGenericBlock('head_user');
            $mergeDatas->addGenericData($user->getFieldsAsArray(), 'head_user');
            $mergeDatas->addGenericBlock('head_group');
            $mergeDatas->addGenericData($group->getFieldsAsArray(), 'head_group');
            $mergeService = \FreeFW\DI\DI::get('FreeOffice::Service::Merge');
            $mergeService->merge($src, $dest, $mergeDatas);
            exec('/usr/bin/unoconv -f pdf -o ' . $dPdf . ' ' . $dest);
            @unlink($dest);
            @unlink($src);
            if (is_file($dPdf)) {
                $filename = $dPdf;
            }
        }
        return $filename;
    }
}
