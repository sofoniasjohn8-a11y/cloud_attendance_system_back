<?php
echo json_encode(['php' => phpversion(), 'extensions' => get_loaded_extensions()]);
