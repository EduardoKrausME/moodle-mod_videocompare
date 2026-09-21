<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings em português do Brasil para Video Compare.
 *
 * @package   mod_videocompare
 * @copyright 2026 Eduardo Kraus
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['addquestion'] = 'Adicionar questão de comparação';
$string['addvideo'] = 'Adicionar vídeo';
$string['allanswered'] = 'Todas as questões obrigatórias respondidas';
$string['answer'] = 'Resposta';
$string['answerquestions'] = 'Questões de comparação';
$string['answerssaved'] = 'Respostas salvas.';
$string['backtoreport'] = 'Voltar ao relatório';
$string['capturedtime'] = 'Momento capturado';
$string['capturetime'] = 'Capturar momento atual';
$string['compareinstructions'] = 'Escolha dois vídeos, capture o momento atual em cada um e escreva a comparação.';
$string['comparemoments'] = 'Comparar momentos';
$string['comparison'] = 'Comparação';
$string['completionpercent'] = 'Progresso geral obrigatório nos vídeos (%)';
$string['completionquestions'] = 'Exigir todas as questões obrigatórias de comparação';
$string['completionquestions_help'] = 'Quando ativado, todas as questões marcadas como obrigatórias precisam ter resposta.';
$string['configureactivity'] = 'Configurar atividade';
$string['deletequestion'] = 'Excluir questão de comparação';
$string['deletevideo'] = 'Excluir vídeo';
$string['description'] = 'Descrição';
$string['displaysettings'] = 'Configurações de exibição';
$string['editquestion'] = 'Editar questão de comparação';
$string['editvideo'] = 'Editar vídeo';
$string['error:captureboth'] = 'Capture o momento atual nos dois vídeos selecionados antes de salvar.';
$string['error:comparisonrequired'] = 'Escreva a comparação antes de salvar.';
$string['error:samemomentvideo'] = 'Escolha dois vídeos diferentes para as referências.';
$string['error:samevideos'] = 'Escolha dois vídeos diferentes.';
$string['errorcompletionpercent'] = 'O percentual de conclusão deve estar entre 1 e 100.';
$string['errorinvalidvimeo'] = 'A URL não contém um identificador válido de vídeo do Vimeo.';
$string['errorinvalidyoutube'] = 'A URL não contém um identificador válido de vídeo do YouTube.';
$string['errortimecode'] = 'Use segundos, MM:SS ou HH:MM:SS.';
$string['erroruploadrequired'] = 'Envie um arquivo de vídeo.';
$string['errorurlrequired'] = 'Informe a URL do vídeo.';
$string['eventcoursemoduleviewed'] = 'Atividade Video Compare visualizada';
$string['jump'] = 'Ir para este momento';
$string['lastposition'] = 'Última posição';
$string['layout'] = 'Layout dos vídeos';
$string['layoutauto'] = 'Automático: lado a lado quando houver espaço';
$string['layoutsidebyside'] = 'Preferir lado a lado';
$string['layoutswitch'] = 'Mostrar um vídeo por vez com alternância rápida';
$string['managecontent'] = 'Gerenciar vídeos e questões';
$string['modulename'] = 'Video Compare';
$string['modulenameplural'] = 'Atividades Video Compare';
$string['movedown'] = 'Mover para baixo';
$string['moveup'] = 'Mover para cima';
$string['nocomparisons'] = 'Nenhuma comparação por momento ainda.';
$string['nocontent'] = 'Nenhum conteúdo disponível.';
$string['notallanswered'] = 'Há questões obrigatórias pendentes';
$string['notconfigured'] = 'Esta atividade precisa de pelo menos dois vídeos antes de os estudantes poderem compará-los.';
$string['optional'] = 'Opcional';
$string['overallprogress'] = 'Progresso geral';
$string['pluginadministration'] = 'Administração do Video Compare';
$string['pluginname'] = 'Video Compare';
$string['privacy:metadata:timecreated'] = 'A data e hora em que o registro foi criado.';
$string['privacy:metadata:timemodified'] = 'A data e hora da última modificação do registro.';
$string['privacy:metadata:videocompare_answers'] = 'Armazena respostas dos estudantes às questões de comparação.';
$string['privacy:metadata:videocompare_answers:answer'] = 'A resposta enviada.';
$string['privacy:metadata:videocompare_answers:answerformat'] = 'O formato de texto usado na resposta enviada.';
$string['privacy:metadata:videocompare_answers:questionid'] = 'A questão respondida.';
$string['privacy:metadata:videocompare_answers:userid'] = 'O usuário que enviou a resposta.';
$string['privacy:metadata:videocompare_answers:videocompareid'] = 'A atividade Video Compare associada à resposta.';
$string['privacy:metadata:videocompare_notes'] = 'Armazena comparações criadas pelos estudantes entre momentos de vídeos.';
$string['privacy:metadata:videocompare_notes:note'] = 'O texto da comparação.';
$string['privacy:metadata:videocompare_notes:timea'] = 'Momento no vídeo A.';
$string['privacy:metadata:videocompare_notes:timeb'] = 'Momento no vídeo B.';
$string['privacy:metadata:videocompare_notes:userid'] = 'O usuário que criou a comparação.';
$string['privacy:metadata:videocompare_notes:videoaid'] = 'O primeiro vídeo referenciado pela comparação.';
$string['privacy:metadata:videocompare_notes:videobid'] = 'O segundo vídeo referenciado pela comparação.';
$string['privacy:metadata:videocompare_notes:videocompareid'] = 'A atividade Video Compare associada à comparação entre momentos.';
$string['privacy:metadata:videocompare_progress'] = 'Armazena o progresso de visualização de cada usuário em cada vídeo.';
$string['privacy:metadata:videocompare_progress:completed'] = 'Indica se o vídeo atingiu o limite interno de visualização.';
$string['privacy:metadata:videocompare_progress:duration'] = 'A duração conhecida do vídeo.';
$string['privacy:metadata:videocompare_progress:lastposition'] = 'A última posição de reprodução.';
$string['privacy:metadata:videocompare_progress:percent'] = 'O percentual assistido.';
$string['privacy:metadata:videocompare_progress:segments'] = 'Os intervalos de tempo assistidos.';
$string['privacy:metadata:videocompare_progress:userid'] = 'O usuário cujo progresso é armazenado.';
$string['privacy:metadata:videocompare_progress:videocompareid'] = 'A atividade Video Compare associada ao registro de progresso.';
$string['privacy:metadata:videocompare_progress:videoid'] = 'O vídeo acompanhado.';
$string['privacy:metadata:videocompare_progress:watchedseconds'] = 'A quantidade de segundos únicos assistidos.';
$string['progress'] = 'Progresso';
$string['questions'] = 'Questões de comparação';
$string['questiontext'] = 'Questão';
$string['referencetimea'] = 'Momento de referência A';
$string['referencetimeb'] = 'Momento de referência B';
$string['referencevideoa'] = 'Vídeo de referência A';
$string['referencevideob'] = 'Vídeo de referência B';
$string['reports'] = 'Relatórios';
$string['required'] = 'Obrigatória';
$string['requiredanswered'] = 'Obrigatórias respondidas';
$string['requiredquestion'] = 'Questão obrigatória';
$string['resume'] = 'Retomar';
$string['saveanswers'] = 'Salvar respostas';
$string['savecomparison'] = 'Salvar comparação';
$string['savedcomparisons'] = 'Suas comparações por momento';
$string['sourcetype'] = 'Fonte do vídeo';
$string['sourceupload'] = 'Vídeo enviado';
$string['sourceuploadoption'] = 'Upload';
$string['sourceurl'] = 'URL do vídeo';
$string['sourceurl_help'] = 'Use uma URL direta de vídeo, URL do YouTube ou URL do Vimeo conforme a fonte escolhida.';
$string['sourceurloption'] = 'URL direta do vídeo';
$string['sourcevimeo'] = 'Vimeo';
$string['sourceyoutube'] = 'YouTube';
$string['student'] = 'Estudante';
$string['studentreport'] = 'Relatório do estudante';
$string['switchvideo'] = 'Alternar vídeo';
$string['timecodehelp'] = 'Use segundos, MM:SS ou HH:MM:SS.';
$string['video'] = 'Vídeo';
$string['videoa'] = 'Vídeo A';
$string['videob'] = 'Vídeo B';
$string['videocompare:addinstance'] = 'Adicionar uma atividade Video Compare';
$string['videocompare:managecontent'] = 'Gerenciar vídeos e questões de comparação';
$string['videocompare:submit'] = 'Enviar comparações e respostas';
$string['videocompare:view'] = 'Visualizar Video Compare';
$string['videocompare:viewreports'] = 'Visualizar relatórios do Video Compare';
$string['videocomparename'] = 'Nome do Video Compare';
$string['videofile'] = 'Arquivo de vídeo';
$string['videoname'] = 'Nome do vídeo';
$string['videos'] = 'Vídeos';
$string['viewdetails'] = 'Ver detalhes';
$string['watched'] = 'Assistido';
