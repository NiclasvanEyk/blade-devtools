function* iterateComponentsTags(rootElem) {
  const iterator = document.createNodeIterator(
    rootElem,
    NodeFilter.SHOW_COMMENT,
    () => NodeFilter.FILTER_ACCEPT
  );

  let curNode;
  let startedNode = null;
  while ((curNode = iterator.nextNode())) {
    const content = curNode.nodeValue.trim();
    if (!content.startsWith("BLADE_COMPONENT_")) {
      continue;
    }

    let { type, data } = parseNode(content);

    if (type === "START") {
      startedNode = { type, id: data, parent: curNode.parentNode };
      continue;
    }

    if (type === "DATA") {
      if (!startedNode) {
        throw new Error("No current node!");
      }
      startedNode.data = JSON.parse(data);

      if (startedNode.label) {
        if (startedNode.data.label.startsWith("components.")) {
          startedNode.data.label = startedNode.data.label.replace(
            "components.",
            ""
          );
        }
        startedNode.data.label = `<x-${startedNode.data.label}>`;
      }

      startedNode.element = nextHtmlTagSibling(curNode);

      yield startedNode;

      continue;
    }

    if (type === "END") {
      startedNode = null;

      yield { type, id: data };
    }
  }

  return;
}

function nextHtmlTagSibling(node) {
  let next = node.nextSibling;

  while (next && ["#comment", "#text"].includes(next.nodeName)) {
    next = next.nextSibling;
  }

  return next;
}

function parseNode(content) {
  const withoutPrefix = content.replace("BLADE_COMPONENT_", "");
  const dataStart = withoutPrefix.indexOf("[") + 1;
  const dataEnd = withoutPrefix.lastIndexOf("]");
  let data = withoutPrefix.slice(dataStart, dataEnd);
  const type = withoutPrefix.substr(0, dataStart - 1);

  return { type, data };
}

/**
 * @param {string} name
 */
function displayLabel(name) {
  const namespace = name.includes("::") ? name.split("::")[0] : null;
  let view = name.includes("::") ? name.split("::")[1] : name;

  if (view.startsWith("components.")) {
    view = view.substring("components.".length);
  }

  if (namespace) {
    return `<x-${namespace}::${view}>`;
  }

  return `<x-${view}>`;
}

function cleanData(data) {
  const cleaned = structuredClone(data);

  delete cleaned["__laravel_slots"];
  delete cleaned["slot"];

  if (Object.keys(cleaned["attributes"]).length === 0) {
    delete cleaned["attributes"];
  }

  return cleaned;
}

export function getAllComments(rootElem) {
  let tree = {
    element: rootElem,
    parent: null,
    children: [],
  };
  let current = tree;

  for (let componentTag of iterateComponentsTags(rootElem)) {
    if (componentTag.type === "START") {
      const component = {
        label: displayLabel(componentTag.data.name),
        element: componentTag.element,
        // id: componentTag.id,
        data: cleanData(componentTag.data.data),
        children: [],
        parent: current,
      };
      current.children.push(component);
      console.log(component);
      current = component;
    }

    if (componentTag.type === "END") {
      current = current.parent;
    }
  }

  return tree;
}
