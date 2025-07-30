// build.js
const fs = require('fs');
const path = require('path');
const { Liquid } = require('liquidjs');

const srcDir = path.join(__dirname);
const distDir = path.join(__dirname, '../public');
const pagesDir = path.join(srcDir, 'pages');
const indexFile = path.join(srcDir, 'index.html');

// Crée une nouvelle instance de LiquidJS
const engine = new Liquid({
    root: [
        srcDir,
    ],
    extname: '.html',
    partials: path.join(srcDir, './_includes'),
    layouts: path.join(srcDir, './_layouts'),
});

// Nettoie le dossier dist (optionnel, mais recommandé)
fs.readdirSync(distDir).forEach(file => {
    // Ignore les fichiers qui ne sont pas des fichiers HTML
    if (!file.endsWith('.html')) {
        return;
    }
    fs.unlinkSync(path.join(distDir, file));
});
console.log('Dossier "public" nettoyé.');

// Traite chaque page source
fs.readdirSync(pagesDir).forEach(pageFile => {
    if (pageFile.endsWith('.html')) {
        const templatePath = path.join('pages', pageFile);
        const outputFilePath = path.join(distDir, 'pages', pageFile);

        engine.renderFile(templatePath, {})
            .then(html => {
                fs.writeFileSync(outputFilePath, html);
                console.log(`Généré : ${outputFilePath}`);
            })
            .catch(error => {
                console.error(`Erreur lors du traitement de ${pageFile}:`, error);
            });
    }
});

// Traite le fichier index.html
const indexOutputFilePath = path.join(distDir, 'index.html');
engine.renderFile(indexFile, {})
    .then(html => {
        fs.writeFileSync(indexOutputFilePath, html);
        console.log(`Généré : ${indexOutputFilePath}`);
    })
    .catch(error => {
        console.error(`Erreur lors du traitement de index.html:`, error);
    });


