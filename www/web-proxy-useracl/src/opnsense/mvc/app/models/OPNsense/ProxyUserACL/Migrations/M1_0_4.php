<?php
/**
 *    Copyright (C) 2017-2021 Smart-Soft
 *
 *    All rights reserved.
 *
 *    Redistribution and use in source and binary forms, with or without
 *    modification, are permitted provided that the following conditions are met:
 *
 *    1. Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *    2. Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 *    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 *    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 *    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 *    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 *    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 *    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 *    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *    POSSIBILITY OF SUCH DAMAGE.
 *
 */
namespace OPNsense\ProxyUserACL\Migrations;

use OPNsense\Base\BaseModelMigration;
use OPNsense\Core\Config;


class M1_0_4 extends BaseModelMigration
{
    public function run($model)
    {
        parent::run($model);

        $mimes_categories = [
            "application" => "application/atom+xml,application/EDI-X12,application/EDIFACT,application/json,application/javascript,application/octet-stream,application/ogg,application/pdf,application/postscript,application/soap+xml,application/font-woff,application/xhtml+xml,application/xml-dtd,application/xop+xml,application/zip,application/gzip,application/x-bittorren,application/x-tex,application/xml",
            "audio" => "audio/basic,audio/L24,audio/mp4,audio/aac,audio/mpeg,audio/ogg,audio/vorbis,audio/x-ms-wma,audio/x-ms-wax,audio/vnd.rn-realaudio,audio/vnd.wave,audio/webm",
            "image" => "image/gif,image/jpeg,image/pjpeg,image/png,image/svg+xml,image/tiff,image/vnd.microsoft.icon,image/vnd.wap.wbmp,image/webp",
            "message" => "message/http,message/imdn+xml,message/partial,message/rfc822",
            "model" => "model/example,model/iges,model/mesh,model/vrml,model/x3d+binary,model/x3d+vrml,model/x3d+xml",
            "multipart" => "multipart/mixed,multipart/alternative,multipart/related,multipart/form-data,multipart/signed,multipart/encrypted",
            "text" => "text/cmd,text/css,text/csv,text/html,text/plain,text/php,text/xml,text/markdown,text/cache-manifest",
            "video" => "video/mpeg,video/mp4,video/ogg,video/quicktime,video/webm,video/x-ms-wmv,video/x-flv,video/3gpp,video/3gpp2",
            "vnd" => "application/vnd.oasis.opendocument.text,application/vnd.oasis.opendocument.spreadsheet,application/vnd.oasis.opendocument.presentation,application/vnd.oasis.opendocument.graphics,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.mozilla.xul+xml,application/vnd.google-earth.kml+xml",
            "x" => "application/x-www-form-urlencoded,application/x-dvi,application/x-latex,application/x-font-ttf,application/x-shockwave-flash,application/x-stuffit,application/x-rar-compressed,application/x-tar,text/x-jquery-tmpl,application/x-javascript",
            "x-pkcs" => "application/x-pkcs12:,application/x-pkcs12,application/x-pkcs7-certificates,application/x-pkcs7-certificates,application/x-pkcs7-certreqresp,application/x-pkcs7-mime,application/x-pkcs7-mime,application/x-pkcs7-signature"
        ];

        foreach ($mimes_categories as $category => $content) {
            $mime = $model->general->Mimes->Mime->add();
            $mime->Description = $category;
            $mime->Names = $content;
        }

        $model->serializeToConfig();
        Config::getInstance()->save();
    }
}
