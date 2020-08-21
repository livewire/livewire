/**
 * I don't want to look at "value" attributes when diffing.
 * I commented out all the lines that compare "value"
 *
 */

export default function morphAttrs(fromNode, toNode) {
    var attrs = toNode.attributes;
    var i;
    var attr;
    var attrName;
    var attrNamespaceURI;
    var attrValue;
    var fromValue;

    // update attributes on original DOM element
    for (i = attrs.length - 1; i >= 0; --i) {
        attr = attrs[i];
        attrName = attr.name;
        attrNamespaceURI = attr.namespaceURI;
        attrValue = attr.value;

        if (attrNamespaceURI) {
            attrName = attr.localName || attrName;
            fromValue = fromNode.getAttributeNS(attrNamespaceURI, attrName);

            if (fromValue !== attrValue) {
                if (attr.prefix === 'xmlns'){
                    attrName = attr.name; // It's not allowed to set an attribute with the XMLNS namespace without specifying the `xmlns` prefix
                }
                fromNode.setAttributeNS(attrNamespaceURI, attrName, attrValue);
            }
        } else {
            fromValue = fromNode.getAttribute(attrName);

            if (fromValue !== attrValue) {
                // @livewireModification: This is the case where we don't want morphdom to pre-emptively add
                // a "display:none" if it's going to be transitioned out by Alpine.
                if (
                    attrName === 'style'
                    && fromNode.__livewire_transition
                    && /display: none;/.test(attrValue)
                ) {
                    delete fromNode.__livewire_transition
                    attrValue = attrValue.replace('display: none;', '')
                }

                fromNode.setAttribute(attrName, attrValue);
            }
        }
    }

    // Remove any extra attributes found on the original DOM element that
    // weren't found on the target element.
    attrs = fromNode.attributes;

    for (i = attrs.length - 1; i >= 0; --i) {
        attr = attrs[i];
        if (attr.specified !== false) {
            attrName = attr.name;
            attrNamespaceURI = attr.namespaceURI;

            if (attrNamespaceURI) {
                attrName = attr.localName || attrName;

                if (!toNode.hasAttributeNS(attrNamespaceURI, attrName)) {
                    fromNode.removeAttributeNS(attrNamespaceURI, attrName);
                }
            } else {
                if (!toNode.hasAttribute(attrName)) {
                    // @livewireModification: This is the case where we don't want morphdom to pre-emptively remove
                    // a "display:none" if it's going to be transitioned in by Alpine.
                    if (
                        attrName === 'style'
                        && fromNode.__livewire_transition
                        && /display: none;/.test(attr.value)
                    ) {
                        delete fromNode.__livewire_transition
                        continue
                    }

                    fromNode.removeAttribute(attrName);
                }
            }
        }
    }
}
