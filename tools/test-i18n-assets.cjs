const {execFileSync} = require('node:child_process');
const path = require('node:path');
const vm = require('node:vm');
const root = process.argv[2];
let consent;
for (const language of ['en', 'fr']) {
    const output = execFileSync('php', [path.join(__dirname, 'test-i18n-assets.php'), root, language], {encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe']});
    const scripts = JSON.parse(output);
    for (const [name, source] of Object.entries(scripts)) {
        if (name === 'privacy-config') {
            if (consent && source.version !== consent.version) throw new Error('Changing language invalidates cookie choices');
            if (consent && source.services.youtube.title === consent.services.youtube.title) throw new Error('Cookie services did not change language');
            consent = source;
            console.log(`PASS ${language} cookie configuration translated with language-neutral consent version`);
            continue;
        }
        new vm.Script(source, {filename: name});
        if (source.includes('<?php')) throw new Error(`Unrendered PHP: ${name}`);
        console.log(`PASS ${language} generated JavaScript: ${name}`);
    }
}
