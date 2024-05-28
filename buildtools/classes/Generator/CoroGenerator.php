<?php

namespace Dpp\Generator;

use Dpp\StructGeneratorInterface;

/**
 * Generate header and .cpp file for coroutine calls (starting with 'co_')
 */
class CoroGenerator implements StructGeneratorInterface
{

    /**
     * @inheritDoc
     */
    public function generateHeaderStart(): string
    {
return <<<EOT
/************************************************************************************
 *
 * D++, A Lightweight C++ library for Discord
 *
 * Copyright 2022 Craig Edwards and D++ contributors
 * (https://github.com/brainboxdotcc/DPP/graphs/contributors)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 ************************************************************************************/


/* Auto @generated by buildtools/make_coro_struct.php.
 *
 * DO NOT EDIT BY HAND!
 *
 * To re-generate this header file re-run the script!
 */

EOT;
    }

    /**
     * @inheritDoc
     */
    public function generateCppStart(): string
    {
        return $this->generateHeaderStart() . <<<EOT
#ifdef DPP_CORO

#include <dpp/export.h>
#include <dpp/snowflake.h>
#include <dpp/cluster.h>
#include <dpp/coro.h>

namespace dpp {


EOT;
    }

    /**
     * @inheritDoc
     */
    public function checkForChanges(): bool
    {
        /* Check if we need to re-generate by comparing modification times */
        $us = file_exists('include/dpp/cluster_coro_calls.h') ? filemtime('include/dpp/cluster_coro_calls.h') : 0;
        $them = filemtime('include/dpp/cluster.h');
        if ($them <= $us) {
            echo "-- No change required.\n";
            return false;
        }

        echo "-- Autogenerating include/dpp/cluster_coro_calls.h\n";
        echo "-- Autogenerating src/dpp/cluster_coro_calls.cpp\n";
        return true;
    }

    /**
     * @inheritDoc
     */
    public function generateHeaderDef(string $returnType, string $currentFunction, string $parameters, string $noDefaults, string $parameterTypes, string $parameterNames): string
    {
        return "[[nodiscard]] async<confirmation_callback_t> co_{$currentFunction}($parameters);\n\n";
    }

    /**
     * @inheritDoc
     */
    public function generateCppDef(string $returnType, string $currentFunction, string $parameters, string $noDefaults, string $parameterTypes, string $parameterNames): string
    {
        /* if (substr($parameterNames, 0, 2) === ", ")
            $parameterNames = substr($parameterNames, 2); */
        return "async<confirmation_callback_t> cluster::co_${currentFunction}($noDefaults) {\n\treturn async{ this, static_cast<void (cluster::*)($parameterTypes". (!empty($parameterTypes) ? ", " : "") . "command_completion_event_t)>(&cluster::$currentFunction)$parameterNames };\n}\n\n";
    }

    /**
     * @inheritDoc
     */
    public function getCommentArray(): array
    {
        return [" * \memberof dpp::cluster"];
    }

    /**
     * @inheritDoc
     */
    public function saveHeader(string $content): void
    {
        $content .= "[[nodiscard]] async<http_request_completion_t> co_request(const std::string &url, http_method method, const std::string &postdata = \"\", const std::string &mimetype = \"text/plain\", const std::multimap<std::string, std::string> &headers = {}, const std::string &protocol = \"1.1\");\n\n";
        file_put_contents('include/dpp/cluster_coro_calls.h', $content);
    }

    /**
     * @inheritDoc
     */
    public function saveCpp(string $cppcontent): void
    {
        $cppcontent .= "dpp::async<dpp::http_request_completion_t> dpp::cluster::co_request(const std::string &url, http_method method, const std::string &postdata, const std::string &mimetype, const std::multimap<std::string, std::string> &headers, const std::string &protocol) {\n\treturn async<http_request_completion_t>{ [&, this] <typename C> (C &&cc) { return this->request(url, method, std::forward<C>(cc), postdata, mimetype, headers, protocol); }};\n}

#endif
";
        file_put_contents('src/dpp/cluster_coro_calls.cpp', $cppcontent);
    }

}
