<?php

$n1 = md5(1);
$n2 = md5(2);

g_start(2, 1);
g_node($n1, 'Node 1');
g_node($n2, 'Node 2');
g_edge('e1', $n1, $n2);
g_end();

/*
g_start(2, 1);
g_node('n1', 'Node 1');
g_node('n2', 'Node 2');
g_edge('e1', 'n1', 'n2');
g_end();
*/

function g_start($nodes, $edges) {
 // $nodes = count($nodes);
 // $edges = count($edges);
 print '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . "\n";

?>
<graphml xmlns="http://graphml.graphdrawing.org/xmlns/graphml" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:y="http://www.yworks.com/xml/graphml" xsi:schemaLocation="http://graphml.graphdrawing.org/xmlns/graphml http://www.yworks.com/xml/schema/graphml/1.0/ygraphml.xsd">
  <key for="node" id="d0" yfiles.type="nodegraphics"/>
  <key attr.name="description" attr.type="string" for="node" id="d1"/>
  <key for="edge" id="d2" yfiles.type="edgegraphics"/>
  <key attr.name="description" attr.type="string" for="edge" id="d3"/>
  <key for="graphml" id="d4" yfiles.type="resources"/>
  <graph edgedefault="directed" id="G" parse.edges="<?= $edges ?>" parse.nodes="<?= $nodes ?>" parse.order="free">
<?
}

function g_end() {
?>
  </graph>
  <data key="d4">
    <y:Resources/>
  </data>
</graphml>
<?
}

function g_node($id, $label) {   
?>
    <node id="<?= $id ?>">
      <data key="d0">
        <y:ShapeNode>
          <y:Geometry height="30.0" width="30.0" x="0.0" y="0.0"/>
          <y:Fill color="#CCCCFF" transparent="false"/>
          <y:BorderStyle color="#000000" type="line" width="1.0"/>
          <y:NodeLabel alignment="center" autoSizePolicy="content" fontFamily="Dialog" fontSize="12" fontStyle="plain" hasBackgroundColor="false" hasLineColor="false" height="4.0" modelName="internal" modelPosition="c" textColor="#000000" visible="true" width="4.0" x="13.0" y="13.0"><?= $label ?></y:NodeLabel>
          <y:Shape type="rectangle"/>
        </y:ShapeNode>
      </data>
      <data key="d1"/>
    </node>
<?
}

function g_edge($id, $source, $target) {
?>
    <edge id="<?= $id ?>" source="<?= $source ?>" target="<?= $target ?>">
      <data key="d2">
        <y:PolyLineEdge>
          <y:Path sx="0.0" sy="0.0" tx="0.0" ty="0.0"/>
          <y:LineStyle color="#000000" type="line" width="1.0"/>
          <y:Arrows source="none" target="standard"/>
          <y:EdgeLabel alignment="center" distance="2.0" fontFamily="Dialog" fontSize="12" fontStyle="plain" hasBackgroundColor="false" hasLineColor="false" height="4.0" modelName="six_pos" modelPosition="tail" preferredPlacement="anywhere" ratio="0.5" textColor="#000000" visible="true" width="4.0" x="5.500000000000014" y="-1.1754258225710146"/>
          <y:BendStyle smoothed="false"/>
        </y:PolyLineEdge>
      </data>
      <data key="d3"/>
    </edge>
<?
}
?>

