#!/usr/bin/env python
# test script by running python script.py devices=test,dummy

import os
import pathlib
import argparse
import sys
import hashlib

# Register NetAlertX directories
INSTALL_PATH="/app"
sys.path.extend([f"{INSTALL_PATH}/front/plugins", f"{INSTALL_PATH}/server"])

from plugin_helper import Plugin_Object, Plugin_Objects, decodeBase64
from logger import mylog, Logger, append_line_to_file
from helper import timeNowTZ, get_setting_value 
from const import logPath, applicationPath
import conf
from pytz import timezone

# Make sure the TIMEZONE for logging is correct
conf.tz = timezone(get_setting_value('TIMEZONE'))

# Make sure log level is initialized correctly
Logger(get_setting_value('LOG_LEVEL'))

pluginName = 'UNDIS'

LOG_PATH = logPath + '/plugins'
LOG_FILE = os.path.join(LOG_PATH, f'script.{pluginName}.log')
RESULT_FILE = os.path.join(LOG_PATH, f'last_result.{pluginName}.log')

def main():

    # the script expects a parameter in the format of devices=device1,device2,...
    parser = argparse.ArgumentParser(description='Import devices from settings')
    parser.add_argument('devices',  action="store",  help="list of device names separated by ','")
    values = parser.parse_args()

    mylog('verbose', [f'[{pluginName}] In script'])

    plugin_objects = Plugin_Objects( RESULT_FILE )

    if values.devices:
        for fake_dev in values.devices.split('=')[1].split(','):

            fake_mac = string_to_mac_hash(fake_dev)

            plugin_objects.add_object(
                primaryId=fake_mac,    # MAC (Device Name)
                secondaryId="0.0.0.0", # IP Address (always 0.0.0.0)
                watched1=fake_dev,     # Device Name
                watched2="",
                watched3="",
                watched4="",
                extra="",
                foreignKey=fake_mac)

    plugin_objects.write_result_file()

    return 0

def string_to_mac_hash(input_string):
    # Calculate a hash using SHA-256
    sha256_hash = hashlib.sha256(input_string.encode()).hexdigest()

    # Take the first 12 characters of the hash and format as a MAC address
    mac_hash = ':'.join(sha256_hash[i:i+2] for i in range(0, 12, 2))
    
    return mac_hash

#===============================================================================
# BEGIN
#===============================================================================
if __name__ == '__main__':
    main()