<?php
require_once '/var/www/html/sistema/vendor/autoload.php';
require_once '/var/www/html/blend/src/blend.php';

require_once BLEND_PATH.'/app.php';

require_once BLEND_PATH.'/cache/cache.php';
require_once BLEND_PATH.'/cache/conninfo.php';
require_once BLEND_PATH.'/cache/service.php';
require_once BLEND_PATH.'/cache/memory.php';
require_once BLEND_PATH.'/cache/storage.php';

require_once BLEND_PATH.'/type/generic.php';
require_once BLEND_PATH.'/validator/validator.php';
require_once BLEND_PATH.'/type/datetime.php';

require_once BLEND_PATH.'/disk/file.php';
require_once BLEND_PATH.'/disk/folder.php';
require_once BLEND_PATH.'/disk/json.php';
require_once BLEND_PATH.'/disk/media.php';

require_once BLEND_PATH.'/datahandle/datahandle.php';
require_once BLEND_PATH.'/datahandle/config.php';
require_once BLEND_PATH.'/datahandle/cookie.php';
require_once BLEND_PATH.'/datahandle/files.php';
require_once BLEND_PATH.'/datahandle/get.php';
require_once BLEND_PATH.'/datahandle/post.php';
require_once BLEND_PATH.'/datahandle/request.php';
require_once BLEND_PATH.'/datahandle/server.php';
require_once BLEND_PATH.'/datahandle/session.php';
require_once BLEND_PATH.'/datahandle/useragent.php';

require_once BLEND_PATH.'/db/conn.php';
require_once BLEND_PATH.'/db/conninfo.php';
require_once BLEND_PATH.'/db/collection.php';
require_once BLEND_PATH.'/db/cond.php';
require_once BLEND_PATH.'/db/where.php';
require_once BLEND_PATH.'/db/model.php';
require_once BLEND_PATH.'/db/constantvalues.php';
require_once BLEND_PATH.'/db/modelapi.php';
require_once BLEND_PATH.'/db/model/history.php';
require_once BLEND_PATH.'/db/filter.php';
require_once BLEND_PATH.'/db/querybuilder.php';
require_once BLEND_PATH.'/db/smartfilter.php';

require_once BLEND_PATH.'/db/column/column.php';
require_once BLEND_PATH.'/db/column/search.php';
require_once BLEND_PATH.'/db/column/collection.php';

require_once BLEND_PATH.'/db/catalog/base.php';
require_once BLEND_PATH.'/db/catalog/mysql.php';

require_once BLEND_PATH.'/fieldlayout/vector.php';

require_once BLEND_PATH.'/misc/timer.php';

require_once BLEND_PATH.'/page/page.php';
require_once BLEND_PATH.'/page/pagepopup.php';
require_once BLEND_PATH.'/page/crud.php';
require_once BLEND_PATH.'/page/cruddropzone.php';
require_once BLEND_PATH.'/page/aftergridcreatecell.php';
require_once BLEND_PATH.'/page/aftergridcreaterow.php';
require_once BLEND_PATH.'/page/beforegridcreaterow.php';
require_once BLEND_PATH.'/page/beforegridexportrow.php';

require_once BLEND_PATH.'/reporttool/engine.php';
require_once BLEND_PATH.'/reporttool/template.php';

require_once BLEND_PATH.'/view/document.php';
require_once BLEND_PATH.'/view/layout.php';
require_once BLEND_PATH.'/view/view.php';

require_once BLEND_PATH.'/component/timeline/dataitem.php';