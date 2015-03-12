/**
 * Retrieves all of the nodes in window.document whose 'data'
 *   attributes contain 3 consecutive ASCII 0x5,0x6,or 0x7
 *
 * @return An array of DOM nodes
 */
function getDocumentNodesWithCode(node) {
    var descs = [];
    node = node || document;
    if(node) {
        var childNodes = node.childNodes;
        for(var i=0; i<childNodes.length; i++) {
            var childNode = childNodes[i];
            var data = childNode.data;
            if(data && data.match && data.match(/[\005-\007]{3}/g)) {
                descs.push(childNode);
            }
            descs = descs.concat(getDocumentNodesWithCode(childNode));
        }
    }
    return descs;
};

/**
 * Queries window.document for a 3-letter non-printing code
 *   The order of the code identifies a type of excerpt (archive, category, etc).
 *   Inserts sharetoolbox and recommendedbox divs on either side of the excerpt.
 *
 * @alters window.document
 */
function addDivsToCodedExcerpts() {
    var excerptNodes = getDocumentNodesWithCode();
    for(var i=0; i<excerptNodes.length; i++) {
        var excerptNode = excerptNodes[i];
        var excerptCode = excerptNode.data.substring(0,3);
        var suffix = "";

        if(excerptCode === String.fromCharCode(5,6,7)) {
            suffix = "-homepage";
        } else if(excerptCode === String.fromCharCode(5,7,6)) {
            suffix = "-page";
        } else if(excerptCode === String.fromCharCode(6,7,5)) {
            suffix = "";
        } else if(excerptCode === String.fromCharCode(6,5,7)) {
            suffix = "-cat-page";
        } else if(excerptCode === String.fromCharCode(7,5,6)) {
            suffix = "-arch-page";
        }

        var parentElement = excerptNode.parentElement;

        var above = document.createElement("div");
        above.className = "at-above-post" + suffix;

        var below = document.createElement("div");
        below.className = "at-below-post" + suffix;

        var aboveRecommended = document.createElement("div");
        aboveRecommended.className = "at-above-post" + suffix + "-recommended";

        var belowRecommended = document.createElement("div");
        belowRecommended.className = "at-below-post" + suffix + "-recommended";

        parentElement.appendChild(below);
        parentElement.appendChild(belowRecommended);

        parentElement.insertBefore(
            above, parentElement.childNodes[0]);
        parentElement.insertBefore(
            aboveRecommended, parentElement.childNodes[0]);
        
        excerptNode.data = excerptNode.data.replace(/[\005-\007]/g, "");
    }
}

addDivsToCodedExcerpts();