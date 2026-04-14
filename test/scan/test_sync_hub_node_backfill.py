"""Tests for update_sync_hub_node backfill."""

import sqlite3
from unittest.mock import patch

from server.scan import device_handling


class DummyDB:
    """Minimal DB wrapper compatible with device_handling helpers."""

    def __init__(self, conn):
        self.sql = conn.cursor()
        self._conn = conn

    def commitDB(self):
        self._conn.commit()


def _make_db(devices):
    """Create an in-memory DB with a Devices table and seed rows."""
    conn = sqlite3.connect(":memory:")
    conn.row_factory = sqlite3.Row
    cur = conn.cursor()

    cur.execute(
        """
        CREATE TABLE Devices (
            devMac TEXT PRIMARY KEY,
            devSyncHubNode TEXT
        )
        """
    )

    cur.executemany(
        "INSERT INTO Devices (devMac, devSyncHubNode) VALUES (?, ?)",
        devices,
    )
    conn.commit()
    return conn


def _read_nodes(conn):
    """Return a dict of devMac -> devSyncHubNode."""
    return {
        row["devMac"]: row["devSyncHubNode"]
        for row in conn.execute("SELECT devMac, devSyncHubNode FROM Devices")
    }


@patch.object(device_handling, "get_setting_value", return_value="MyNode")
def test_backfill_empty_values(mock_setting):
    """Empty and null devSyncHubNode should be backfilled with SYNC_node_name."""
    conn = _make_db([
        ("AA:AA:AA:AA:AA:01", ""),
        ("AA:AA:AA:AA:AA:02", None),
        ("AA:AA:AA:AA:AA:03", "null"),
    ])

    device_handling.update_sync_hub_node(DummyDB(conn))
    nodes = _read_nodes(conn)

    assert nodes["AA:AA:AA:AA:AA:01"] == "MyNode"
    assert nodes["AA:AA:AA:AA:AA:02"] == "MyNode"
    assert nodes["AA:AA:AA:AA:AA:03"] == "MyNode"


@patch.object(device_handling, "get_setting_value", return_value="MyNode")
def test_no_overwrite_existing(mock_setting):
    """Devices with a real devSyncHubNode should not be overwritten."""
    conn = _make_db([
        ("AA:AA:AA:AA:AA:01", "RemoteNode"),
        ("AA:AA:AA:AA:AA:02", ""),
    ])

    device_handling.update_sync_hub_node(DummyDB(conn))
    nodes = _read_nodes(conn)

    assert nodes["AA:AA:AA:AA:AA:01"] == "RemoteNode"
    assert nodes["AA:AA:AA:AA:AA:02"] == "MyNode"


@patch.object(device_handling, "get_setting_value", return_value="")
def test_noop_when_setting_empty(mock_setting):
    """No updates when SYNC_node_name is empty."""
    conn = _make_db([
        ("AA:AA:AA:AA:AA:01", ""),
        ("AA:AA:AA:AA:AA:02", None),
    ])

    device_handling.update_sync_hub_node(DummyDB(conn))
    nodes = _read_nodes(conn)

    assert nodes["AA:AA:AA:AA:AA:01"] == ""
    assert nodes["AA:AA:AA:AA:AA:02"] is None


@patch.object(device_handling, "get_setting_value", return_value=None)
def test_noop_when_setting_none(mock_setting):
    """No updates when SYNC_node_name is None."""
    conn = _make_db([
        ("AA:AA:AA:AA:AA:01", ""),
    ])

    device_handling.update_sync_hub_node(DummyDB(conn))
    nodes = _read_nodes(conn)

    assert nodes["AA:AA:AA:AA:AA:01"] == ""
