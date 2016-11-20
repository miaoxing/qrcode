<?php

namespace Miaoxing\Qrcode\Controller;

class Qrcode extends \miaoxing\plugin\BaseController
{
    protected $guestPages =  ['qrcode/show'];

    public function showAction($req)
    {
        $validator = wei()->validate([
            'data' => $req,
            'rules' => [
                'text' => [

                ]
            ],
            'names' => [
                'text' => '文本内容'
            ]
        ]);
        if (!$validator->isValid()) {
            return $this->err($validator->getFirstMessage());
        }

        // 文本内容
        $text = $req['text'] ?: '';

        // 相对大小,内容越多,图片越大
        $size = $req['size'] ?: 3;

        // 精度越高,图片越大,识别率越高,取值为0 - 4
        $level = $req['level'] ?: 3;

        $logo = $req['logo'];

        $logoSize = $req['logoSize'] ?: 30;

        // 生成二维码图片资源对象
        $enc = \QRencode::factory($level, $size, 0);
        $tab = $enc->encode($text);
        $maxSize = (int)(QR_PNG_MAXIMUM_SIZE / (count($tab)));
        $image = \QRimage::image($tab, min($size, $maxSize), 0);

        // TODO 支持远程图片
        // 将LOGO放置到图片中间
        /*if ($logo && substr($logo, 0, 4) != 'http') {
            if (!file_exists($logo)) {
                return $this->err('图片无效或者不存在');
            }

            $logoImg = null;
            switch (strtolower(substr($logo, strrpos($logo, '.') + 1))) {
                case 'jpg':
                    $logoImg = imagecreatefromjpeg($logo);
                    break;
                case 'png':
                    $logoImg = imagecreatefrompng($logo);
                    break;
                case 'gif':
                    $logoImg = imagecreatefromgif($logo);
                    break;
                default:
                    return $this->err('不支持该图片类型');
            }

            $bgX = imagesx($image);
            $bgY = imagesy($image);
            $logoX = imagesx($logoImg);
            $logoY = imagesy($logoImg);

            // 缩放logo
            $tmpImg = imagecreatetruecolor($logoSize, $logoSize);
            imagecopyresampled($tmpImg, $logoImg, 0, 0, 0, 0, $logoSize, $logoSize, $logoX, $logoY);
            imagecopyresized($image, $tmpImg, ($bgX - $logoSize) / 2, ($bgY - $logoSize) / 2, 0, 0, $logoSize, $logoSize, $logoSize, $logoSize);
            imagedestroy($logoImg);
            imagedestroy($tmpImg);
        }*/

        // 生成图片内容
        ob_start();
        imagepng($image);
        $content = ob_get_clean();
        imagedestroy($image);

        if (!$req['download']) {
            // 展示图片
            $this->response->setHeader('Content-type', 'image/png');
        } else {
            // 下载图片
            $this->response->setHeader(array(
                'Content-Description' => 'File Transfer',
                'Content-Type' => 'application/x-download',
                'Content-Disposition' => 'attachment;filename=qrcode.png',
                'Content-Transfer-Encoding' => 'binary',
                'Expires' => '0',
                'Cache-Control' => 'must-revalidate',
                'Pragma' => 'public',
                'Content-Length' => strlen($content),
            ));
        }

        return $content;
    }
}
