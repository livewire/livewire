// https://developer.mozilla.org/en-US/docs/Web/API/Element/getAttributeNames#Polyfill
if (Element.prototype.getAttributeNames == undefined) {
    Element.prototype.getAttributeNames = function () {
        var attributes = this.attributes;
        var length = attributes.length;
        var result = new Array(length);
        for (var i = 0; i < length; i++) {
            result[i] = attributes[i].name;
        }
        return result;
    };
}
