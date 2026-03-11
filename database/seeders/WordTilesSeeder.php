<?php

namespace Database\Seeders;

use App\Models\WordTile;
use Illuminate\Database\Seeder;

class WordTilesSeeder extends Seeder
{
    public function run(): void
    {
        $words = [
            // Keywords & control flow
            'if', 'else', 'for', 'while', 'do', 'switch', 'case', 'break', 'continue', 'return',
            'try', 'catch', 'throw', 'finally', 'async', 'await', 'yield',
            'function', 'class', 'interface', 'extends', 'implements', 'new', 'this', 'super',
            'const', 'let', 'var', 'static', 'public', 'private', 'protected', 'abstract', 'final',
            'import', 'export', 'from', 'default', 'true', 'false', 'null', 'undefined',
            'and', 'or', 'not', 'in', 'of', 'typeof', 'instanceof', 'delete', 'void',
            // Types & values
            'string', 'number', 'boolean', 'array', 'object', 'null', 'undefined',
            'int', 'float', 'double', 'bool', 'char', 'void', 'any', 'never',
            'type', 'interface', 'enum', 'union', 'tuple', 'generic',
            // Common identifiers / concepts
            'data', 'value', 'result', 'response', 'request', 'error', 'message',
            'user', 'id', 'name', 'email', 'password', 'token', 'key', 'index',
            'list', 'item', 'element', 'node', 'child', 'parent', 'root',
            'length', 'size', 'count', 'total', 'sum', 'min', 'max',
            'get', 'set', 'add', 'remove', 'update', 'create', 'delete', 'find',
            'load', 'save', 'fetch', 'send', 'post', 'put', 'patch',
            'start', 'end', 'begin', 'stop', 'run', 'execute', 'call', 'invoke',
            'init', 'config', 'options', 'params', 'args', 'callback',
            'handler', 'listener', 'event', 'emit', 'trigger', 'dispatch',
            'state', 'props', 'ref', 'memo', 'effect', 'reducer',
            'query', 'mutation', 'resolver', 'schema', 'model', 'entity',
            'buffer', 'stream', 'pipe', 'encode', 'decode', 'parse', 'stringify',
            'map', 'filter', 'reduce', 'sort', 'slice', 'push', 'pop', 'shift',
            'then', 'catch', 'resolve', 'reject', 'promise', 'async',
            // Tech & tools
            'api', 'url', 'path', 'route', 'endpoint', 'method', 'header', 'body',
            'server', 'client', 'database', 'cache', 'session', 'cookie',
            'file', 'dir', 'path', 'read', 'write', 'append', 'open', 'close',
            'hash', 'encrypt', 'decrypt', 'sign', 'verify', 'salt',
            'log', 'debug', 'trace', 'warn', 'info', 'error', 'stack',
            'test', 'mock', 'stub', 'spy', 'assert', 'expect', 'describe', 'it',
            'build', 'deploy', 'release', 'version', 'branch', 'commit', 'merge',
            'docker', 'container', 'image', 'volume', 'network', 'port',
            'git', 'repo', 'clone', 'pull', 'push', 'status', 'diff',
            'npm', 'yarn', 'package', 'module', 'dependency', 'devDependency',
            'bundle', 'compile', 'transpile', 'minify', 'webpack', 'vite',
            // Programming-adjacent / phrases
            'bug', 'fix', 'patch', 'hotfix', 'feature', 'refactor', 'legacy',
            'stack', 'overflow', 'heap', 'memory', 'garbage', 'collector',
            'loop', 'iterate', 'recursion', 'recursive', 'base', 'case',
            'scope', 'closure', 'lexical', 'hoisting', 'binding',
            'sync', 'async', 'blocking', 'nonblocking', 'concurrent', 'parallel',
            'race', 'condition', 'deadlock', 'mutex', 'lock', 'thread',
            'crash', 'timeout', 'retry', 'backoff', 'throttle', 'debounce',
            'cache', 'hit', 'miss', 'invalidate', 'expire', 'ttl',
            'index', 'query', 'join', 'where', 'select', 'insert', 'update',
            'primary', 'foreign', 'key', 'constraint', 'migration', 'seed',
            'middleware', 'guard', 'policy', 'gate', 'sanctum',
            'blade', 'component', 'slot', 'directive', 'layout',
            'vue', 'react', 'angular', 'svelte', 'component', 'props',
        ];

        foreach ($words as $word) {
            WordTile::firstOrCreate(['word' => $word]);
        }
    }
}
